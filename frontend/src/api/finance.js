import api from "../services/api";

export const getLoanProducts = (params) => api.get("/loan-products", { params });
export const getLoanProduct = (id) => api.get(`/loan-products/${id}`);

export const getLoanApplications = (params) => api.get("/loan-applications", { params });
export const getLoanApplication = (id) => api.get(`/loan-applications/${id}`);
export const createLoanApplication = (data) => api.post("/loan-applications", data);
export const submitLoanApplication = (id) => api.post(`/loan-applications/${id}/submit`);

export const getWallet = () => api.get("/wallet");
export const getWalletTransactions = (params) => api.get("/wallet/transactions", { params });
export const getWalletSummary = () => api.get("/wallet/summary");
export const requestWithdrawal = (data) => api.post("/wallet/withdraw", data);
