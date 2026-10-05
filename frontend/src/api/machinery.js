import axios from "./axios";

export function getMachinery(params = {}) {
    return axios.get("/machinery", {
        params
    });
}

export function getMachineryDetails(id) {
    return axios.get(`/machinery/${id}`);
}

export function getFeaturedMachinery() {
    return axios.get("/machinery-featured");
}

export function getRelatedMachinery(id) {
    return axios.get(`/machinery/${id}/related`);
}

export function getMachineryCategories() {
    return axios.get("/machinery-categories");
}

export function getMachineryBrands() {
    return axios.get("/machinery-brands");
}

export function getMachineryModels(params = {}) {
    return axios.get("/machinery-models", {
        params
    });
}

export function bookMachinery(data) {
    return axios.post("/machinery-bookings", data);
}

export function getReviews(listingId) {
    return axios.get("/machinery-reviews", {
        params: {
            listing_id: listingId
        }
    });
}

export function createReview(data) {
    return axios.post("/machinery-reviews", data);
}