import api from "./index";

export function getLivestockListings() {
    return api.get("/livestock");
}

export function getLivestock(id) {
    return api.get(`/livestock/${id}`);
}