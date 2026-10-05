import api from "../services/api";

export const getAnalyticsCatalogue = () => api.get("/analytics");
export const getPlatformOverview = () => api.get("/analytics/platform");
export const getMarketplaceReport = (params) => api.get("/analytics/marketplace", { params });
export const getFarmProductionReport = (params) => api.get("/analytics/farm-production", { params });
export const getForecast = (params) => api.get("/analytics/forecast", { params });
export const getEsgSnapshot = (params) => api.get("/analytics/esg", { params });
export const getFarmerDashboard = () => api.get("/analytics/farmer-dashboard");
