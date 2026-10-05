<template>
  <div class="border rounded-lg overflow-hidden bg-white">
    <div class="flex flex-wrap items-center gap-2 p-2 border-b bg-gray-50 text-sm">
      <button type="button" class="px-3 py-1 rounded bg-green-600 text-white font-medium" :disabled="!drawing" @click="finish">
        Finish polygon
      </button>
      <button type="button" class="px-3 py-1 rounded border" @click="startDraw">
        {{ points.length ? "Redraw" : "Draw boundary" }}
      </button>
      <button type="button" class="px-3 py-1 rounded border text-red-600" @click="clear" :disabled="!points.length">Clear</button>
      <button type="button" class="px-3 py-1 rounded border" @click="useMyLocation">My location</button>
      <span class="text-xs text-gray-500 ml-auto">
        {{ points.length }} points
        <span v-if="previewHa != null"> · ~{{ previewHa }} ha</span>
      </span>
    </div>
    <p class="text-xs text-gray-500 px-3 py-1">Tap the map to add corners. Need at least 3 points, then Finish.</p>
    <div ref="mapEl" class="w-full h-64 sm:h-80 bg-gray-100"></div>
    <div class="p-2 border-t flex gap-2">
      <button
        type="button"
        class="flex-1 bg-green-700 text-white py-2 rounded font-semibold disabled:opacity-40"
        :disabled="points.length < 3 || saving"
        @click="save"
      >
        {{ saving ? "Saving…" : "Save boundary" }}
      </button>
    </div>
    <p v-if="msg" class="text-xs px-3 pb-2" :class="msgErr ? 'text-red-600' : 'text-green-700'">{{ msg }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
  /** GeoJSON polygon or null */
  modelValue: { type: Object, default: null },
  center: { type: Object, default: () => ({ lat: -6.8, lng: 39.2 }) },
  saving: { type: Boolean, default: false },
});
const emit = defineEmits(["save", "update:modelValue"]);

const mapEl = ref(null);
let map = null;
let polygonLayer = null;
let markers = [];
const points = ref([]); // {lat, lng}[]
const drawing = ref(false);
const msg = ref("");
const msgErr = ref(false);

const previewHa = computed(() => {
  if (points.value.length < 3) return null;
  const m2 = ringAreaM2(points.value);
  return (m2 / 10000).toFixed(3);
});

function ringAreaM2(pts) {
  const R = 6371008.8;
  const n = pts.length;
  let total = 0;
  for (let i = 0; i < n; i++) {
    const a = pts[i];
    const b = pts[(i + 1) % n];
    const lat1 = (a.lat * Math.PI) / 180;
    const lat2 = (b.lat * Math.PI) / 180;
    const lng1 = (a.lng * Math.PI) / 180;
    const lng2 = (b.lng * Math.PI) / 180;
    total += (lng2 - lng1) * (2 + Math.sin(lat1) + Math.sin(lat2));
  }
  return Math.abs((total * R * R) / 2);
}

function toGeoJson() {
  const ring = points.value.map((p) => [p.lng, p.lat]);
  if (ring.length) {
    const f = ring[0];
    const l = ring[ring.length - 1];
    if (f[0] !== l[0] || f[1] !== l[1]) ring.push([...f]);
  }
  return { type: "Polygon", coordinates: [ring] };
}

function loadFromModel() {
  const b = props.modelValue;
  if (!b?.coordinates?.[0]?.length) return;
  points.value = b.coordinates[0]
    .map((c) => ({ lat: c[1], lng: c[0] }))
    .filter((_, i, arr) => i < arr.length - 1 || arr.length === 1);
  redraw();
}

function clearLayers() {
  if (polygonLayer) {
    map.removeLayer(polygonLayer);
    polygonLayer = null;
  }
  markers.forEach((m) => map.removeLayer(m));
  markers = [];
}

function redraw() {
  if (!map) return;
  clearLayers();
  points.value.forEach((p, i) => {
    const m = L.circleMarker([p.lat, p.lng], {
      radius: 6,
      color: "#15803d",
      fillColor: "#22c55e",
      fillOpacity: 1,
    }).addTo(map);
    m.bindTooltip(`#${i + 1}`, { permanent: false });
    markers.push(m);
  });
  if (points.value.length >= 2) {
    const latlngs = points.value.map((p) => [p.lat, p.lng]);
    polygonLayer = L.polygon(latlngs, {
      color: "#15803d",
      weight: 2,
      fillColor: "#86efac",
      fillOpacity: 0.35,
    }).addTo(map);
  }
}

function startDraw() {
  points.value = [];
  drawing.value = true;
  msg.value = "Tap map to add corners…";
  msgErr.value = false;
  redraw();
}

function finish() {
  drawing.value = false;
  if (points.value.length < 3) {
    msgErr.value = true;
    msg.value = "Need at least 3 points";
    return;
  }
  msg.value = "Polygon ready — click Save boundary";
  msgErr.value = false;
  emit("update:modelValue", toGeoJson());
}

function clear() {
  points.value = [];
  drawing.value = true;
  redraw();
  emit("update:modelValue", null);
}

function useMyLocation() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition((pos) => {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    map.setView([lat, lng], 16);
  });
}

function save() {
  if (points.value.length < 3) {
    msgErr.value = true;
    msg.value = "Need at least 3 points";
    return;
  }
  const geo = toGeoJson();
  emit("update:modelValue", geo);
  emit("save", geo);
}

onMounted(() => {
  const c = props.center || {};
  const lat = Number(c.lat ?? c.latitude ?? -6.8);
  const lng = Number(c.lng ?? c.longitude ?? 39.2);
  map = L.map(mapEl.value).setView([lat, lng], 14);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap",
    maxZoom: 19,
  }).addTo(map);

  map.on("click", (e) => {
    if (!drawing.value && points.value.length >= 3) {
      // allow continue adding if user wants
    }
    drawing.value = true;
    points.value.push({ lat: e.latlng.lat, lng: e.latlng.lng });
    redraw();
  });

  loadFromModel();
  if (points.value.length >= 3) {
    const bounds = L.latLngBounds(points.value.map((p) => [p.lat, p.lng]));
    map.fitBounds(bounds.pad(0.2));
  }
  setTimeout(() => map.invalidateSize(), 200);
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
  }
});

watch(
  () => props.modelValue,
  () => {
    if (!drawing.value) loadFromModel();
  }
);
</script>
