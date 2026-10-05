import api from "../services/api";

export const getKnowledgeHub = () => api.get("/knowledge-hub");
export const searchKnowledge = (q) => api.get("/knowledge-hub/search", { params: { q } });

export const getCourses = (params) => api.get("/training-courses", { params });
export const getCourse = (idOrSlug) => api.get(`/training-courses/${idOrSlug}`);
export const enrollCourse = (id) => api.post(`/training-courses/${id}/enroll`);
export const updateCourseProgress = (id, progress_percent) =>
  api.post(`/training-courses/${id}/progress`, { progress_percent });
export const getMyEnrollments = () => api.get("/my-course-enrollments");

export const getAIRecommendations = (params) => api.get("/ai-recommendations", { params });
export const generateAIRecommendation = (data) => api.post("/ai-recommendations/generate", data);
export const acceptAIRecommendation = (id) => api.post(`/ai-recommendations/${id}/accept`);

export const getExtensionOfficers = (params) => api.get("/extension-officers", { params });
export const createAdvisoryRequest = (data) => api.post("/advisory-requests", data);
export const getMyAdvisoryRequests = () => api.get("/my-advisory-requests");
