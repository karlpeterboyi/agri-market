import api from "../services/api";

export const getAnnouncements = (params) => api.get("/government/announcements", { params });
export const getAnnouncement = (idOrSlug) => api.get(`/government/announcements/${idOrSlug}`);

export const getSubsidyPrograms = (params) => api.get("/subsidy-programs", { params });
export const getSubsidyProgram = (id) => api.get(`/subsidy-programs/${id}`);

export const getSubsidyApplications = (params) => api.get("/subsidy-applications", { params });
export const createSubsidyApplication = (data) => api.post("/subsidy-applications", data);
export const submitSubsidyApplication = (id) => api.post(`/subsidy-applications/${id}/submit`);

export const getRegistrations = (params) => api.get("/agricultural-registrations", { params });
export const createRegistration = (data) => api.post("/agricultural-registrations", data);

export const getAgriculturalStatistics = (params) => api.get("/agricultural-statistics", { params });
export const getFoodSecurity = (params) => api.get("/agricultural-statistics/food-security", { params });
export const getGovernmentDashboard = () => api.get("/government/dashboard");
