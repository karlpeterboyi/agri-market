import api from "../services/api";

export const getFarms = (params) => api.get("/farms", { params });
export const getFarm = (id) => api.get(`/farms/${id}`);
export const createFarm = (data) => api.post("/farms", data);
export const updateFarm = (id, data) => api.put(`/farms/${id}`, data);
export const deleteFarm = (id) => api.delete(`/farms/${id}`);

export const getFieldBlocks = (params) => api.get("/field-blocks", { params });
export const createFieldBlock = (data) => api.post("/field-blocks", data);

export const getCropCycles = (params) => api.get("/crop-cycles", { params });
export const createCropCycle = (data) => api.post("/crop-cycles", data);
export const harvestCropCycle = (id, data) => api.post(`/crop-cycles/${id}/harvest`, data);

export const getFarmActivities = (params) => api.get("/farm-activities", { params });
export const createFarmActivity = (data) => api.post("/farm-activities", data);
export const getActivityCostSummary = (params) => api.get("/farm-activities-cost-summary", { params });

export const getWarehouses = (params) => api.get("/farm-warehouses", { params });
export const createWarehouse = (data) => api.post("/farm-warehouses", data);
export const getInventoryItems = (params) => api.get("/inventory-items", { params });
export const createInventoryItem = (data) => api.post("/inventory-items", data);
