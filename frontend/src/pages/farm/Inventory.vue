<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h2 class="text-2xl sm:text-3xl font-bold">{{ t('inventory.title') }}</h2>
      <div class="flex flex-wrap gap-2">
        <button @click="showWhForm = !showWhForm" class="bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">
          {{ showWhForm ? t('common.cancel') : '+ ' + t('inventory.addWarehouse') }}
        </button>
        <button @click="showItemForm = !showItemForm" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
          {{ showItemForm ? t('common.cancel') : '+ ' + t('inventory.addItem') }}
        </button>
      </div>
    </div>

    <!-- Warehouse form -->
    <form v-if="showWhForm" @submit.prevent="saveWarehouse" class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 grid sm:grid-cols-2 gap-3">
      <select v-model="whForm.farm_id" required class="border rounded px-3 py-2">
        <option disabled value="">{{ t('inventory.selectFarm') }}</option>
        <option v-for="f in farms" :key="f.id" :value="f.id">{{ f.name }}</option>
      </select>
      <input v-model="whForm.name" required :placeholder="t('inventory.warehouseName')" class="border rounded px-3 py-2" />
      <select v-model="whForm.type" class="border rounded px-3 py-2">
        <option value="main">Main</option>
        <option value="cold_storage">Cold storage</option>
        <option value="input_store">Input store</option>
        <option value="chemical_store">Chemical store</option>
      </select>
      <input v-model="whForm.location" :placeholder="t('common.location')" class="border rounded px-3 py-2" />
      <button type="submit" class="sm:col-span-2 bg-green-600 text-white rounded px-4 py-2 font-semibold">{{ t('common.save') }}</button>
    </form>

    <!-- Item form -->
    <form v-if="showItemForm" @submit.prevent="saveItem" class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 grid sm:grid-cols-2 gap-3">
      <select v-model="itemForm.farm_warehouse_id" required class="border rounded px-3 py-2">
        <option disabled value="">{{ t('inventory.selectWarehouse') }}</option>
        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }} ({{ w.farm?.name }})</option>
      </select>
      <input v-model="itemForm.name" required :placeholder="t('inventory.itemName')" class="border rounded px-3 py-2" />
      <select v-model="itemForm.category" required class="border rounded px-3 py-2">
        <option value="seed">Seed</option>
        <option value="fertilizer">Fertilizer</option>
        <option value="pesticide">Pesticide</option>
        <option value="feed">Feed</option>
        <option value="fuel">Fuel</option>
        <option value="tool">Tool</option>
        <option value="other">Other</option>
      </select>
      <input v-model="itemForm.unit" required placeholder="Unit (kg, bag, L…)" class="border rounded px-3 py-2" />
      <input v-model.number="itemForm.quantity" type="number" step="0.01" required :placeholder="t('inventory.quantity')" class="border rounded px-3 py-2" />
      <input v-model.number="itemForm.minimum_quantity" type="number" step="0.01" :placeholder="t('inventory.minQty')" class="border rounded px-3 py-2" />
      <input v-model.number="itemForm.unit_cost" type="number" step="0.01" :placeholder="t('inventory.unitCost')" class="border rounded px-3 py-2" />
      <input v-model="itemForm.supplier" :placeholder="t('inventory.supplier')" class="border rounded px-3 py-2" />
      <button type="submit" class="sm:col-span-2 bg-green-600 text-white rounded px-4 py-2 font-semibold">{{ t('common.save') }}</button>
    </form>

    <!-- Warehouses -->
    <h3 class="text-lg font-semibold mb-3">{{ t('inventory.warehouses') }}</h3>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
      <div v-for="w in warehouses" :key="w.id" class="bg-white rounded-lg shadow p-4">
        <p class="font-bold">{{ w.name }}</p>
        <p class="text-sm text-gray-500">{{ w.farm?.name }} · {{ w.type }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ w.location || '—' }}</p>
      </div>
      <p v-if="!warehouses.length" class="text-gray-400 text-sm col-span-full">{{ t('inventory.noWarehouses') }}</p>
    </div>

    <!-- Inventory table -->
    <h3 class="text-lg font-semibold mb-3">{{ t('inventory.items') }}</h3>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[600px]">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="p-3">{{ t('inventory.itemName') }}</th>
            <th class="p-3">{{ t('common.category') }}</th>
            <th class="p-3">{{ t('inventory.quantity') }}</th>
            <th class="p-3">{{ t('inventory.minQty') }}</th>
            <th class="p-3">{{ t('inventory.warehouse') }}</th>
            <th class="p-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="border-t">
            <td class="p-3 font-medium">{{ item.name }}</td>
            <td class="p-3">{{ item.category }}</td>
            <td class="p-3">{{ item.quantity }} {{ item.unit }}</td>
            <td class="p-3">{{ item.minimum_quantity ?? '—' }}</td>
            <td class="p-3">{{ item.warehouse?.name || '—' }}</td>
            <td class="p-3">
              <span v-if="item.minimum_quantity != null && item.quantity <= item.minimum_quantity"
                class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded">{{ t('inventory.lowStock') }}</span>
              <span v-else class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">OK</span>
            </td>
          </tr>
          <tr v-if="!items.length"><td colspan="6" class="p-4 text-gray-400">{{ t('inventory.noItems') }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "../../i18n";
import { getFarms, getWarehouses, createWarehouse, getInventoryItems, createInventoryItem } from "../../api/farms";

const { t } = useI18n();
const farms = ref([]);
const warehouses = ref([]);
const items = ref([]);
const showWhForm = ref(false);
const showItemForm = ref(false);
const whForm = ref({ farm_id: "", name: "", type: "main", location: "" });
const itemForm = ref({
  farm_warehouse_id: "", name: "", category: "seed", unit: "kg",
  quantity: null, minimum_quantity: null, unit_cost: null, supplier: "",
});

async function load() {
  const [f, w, i] = await Promise.all([getFarms(), getWarehouses(), getInventoryItems()]);
  farms.value = f.data.data || f.data;
  warehouses.value = w.data.data || w.data;
  items.value = i.data.data || i.data;
}

async function saveWarehouse() {
  await createWarehouse(whForm.value);
  showWhForm.value = false;
  whForm.value = { farm_id: "", name: "", type: "main", location: "" };
  await load();
}

async function saveItem() {
  await createInventoryItem(itemForm.value);
  showItemForm.value = false;
  itemForm.value = { farm_warehouse_id: "", name: "", category: "seed", unit: "kg", quantity: null, minimum_quantity: null, unit_cost: null, supplier: "" };
  await load();
}

onMounted(load);
</script>
