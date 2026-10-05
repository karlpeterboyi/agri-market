<template>
  <div>

    <button
      @click="show=true"
      class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold"
    >
      Book Machinery
    </button>

    <div
      v-if="show"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl w-full max-w-lg p-6">

        <h2 class="text-2xl font-bold mb-6">
          Book Machinery
        </h2>

        <div class="space-y-4">

          <div>
            <label>Start Date</label>

            <input
              type="date"
              v-model="form.start_date"
              class="w-full border rounded-lg p-2"
            >
          </div>

          <div>
            <label>End Date</label>

            <input
              type="date"
              v-model="form.end_date"
              class="w-full border rounded-lg p-2"
            >
          </div>

          <label class="flex items-center gap-2">

            <input
              type="checkbox"
              v-model="form.operator_required"
            >

            Operator Required

          </label>

          <textarea
            v-model="form.notes"
            rows="4"
            placeholder="Additional notes"
            class="w-full border rounded-lg p-2"
          />

        </div>

        <div class="mt-6 flex justify-end gap-3">

          <button
            @click="show=false"
            class="border px-4 py-2 rounded"
          >
            Cancel
          </button>

          <button
            @click="submit"
            class="bg-green-600 text-white px-4 py-2 rounded"
          >
            Submit Booking
          </button>

        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from "vue";

import { bookMachinery } from "../api/machinery";

const props = defineProps({
    listingId: Number
});

const show = ref(false);

const form = ref({
    start_date:"",
    end_date:"",
    operator_required:false,
    notes:""
});

async function submit(){

    await bookMachinery({

        machinery_listing_id:props.listingId,

        ...form.value

    });

    alert("Booking submitted successfully.");

    show.value=false;

}
</script>