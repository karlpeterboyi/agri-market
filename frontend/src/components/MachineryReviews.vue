<template>
<div class="bg-white rounded-xl shadow p-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Customer Reviews
        </h2>

        <div class="text-yellow-500 text-lg font-semibold">
            ⭐ {{ averageRating }}
            <span class="text-gray-500 text-base">
                ({{ reviews.length }} Reviews)
            </span>
        </div>

    </div>

    <!-- Add Review -->

    <div class="border rounded-lg p-4 mb-8">

        <h3 class="font-semibold mb-3">
            Leave a Review
        </h3>

        <select
            v-model="form.rating"
            class="w-full border rounded-lg p-2 mb-3"
        >
            <option value="">Rating</option>

            <option
                v-for="i in 5"
                :key="i"
                :value="i"
            >
                {{ i }} Star{{ i>1 ? "s":"" }}
            </option>

        </select>

        <textarea
            rows="4"
            v-model="form.review"
            class="w-full border rounded-lg p-2"
            placeholder="Share your experience..."
        />

        <button
            @click="submit"
            class="mt-3 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg"
        >
            Submit Review
        </button>

    </div>

    <!-- Reviews -->

    <div
        v-if="reviews.length"
        class="space-y-6"
    >

        <div
            v-for="review in reviews"
            :key="review.id"
            class="border-b pb-5"
        >

            <div class="flex justify-between">

                <div>

                    <h4 class="font-semibold">

                        {{ review.user?.name }}

                    </h4>

                    <div class="text-yellow-500">

                        {{ stars(review.rating) }}

                    </div>

                </div>

                <div class="text-sm text-gray-500">

                    {{ formatDate(review.created_at) }}

                </div>

            </div>

            <p class="mt-3 text-gray-700">

                {{ review.review }}

            </p>

        </div>

    </div>

    <div
        v-else
        class="text-center py-8 text-gray-500"
    >
        No reviews yet.
    </div>

</div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

import {
    getReviews,
    createReview
} from "../api/machinery";

const props = defineProps({
    listingId: Number
});

const reviews = ref([]);

const form = ref({
    rating: "",
    review: ""
});

const averageRating = computed(() => {

    if (!reviews.value.length) return "0.0";

    const total = reviews.value.reduce((sum, r) => {
        return sum + Number(r.rating);
    }, 0);

    return (total / reviews.value.length).toFixed(1);

});

function stars(value) {
    return "⭐".repeat(value);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString();
}

async function loadReviews() {

    const res = await getReviews(props.listingId);

    reviews.value = res.data;

}

async function submit() {

    if (!form.value.rating) {

        alert("Please select a rating.");

        return;

    }

    await createReview({

        machinery_listing_id: props.listingId,

        rating: form.value.rating,

        review: form.value.review

    });

    form.value = {
        rating: "",
        review: ""
    };

    await loadReviews();

}

onMounted(loadReviews);
</script>