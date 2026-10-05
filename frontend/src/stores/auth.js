import { defineStore } from "pinia";
// Replaced authApi with the new API client as requested
import api from "../services/api";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: localStorage.getItem("token")
    }),

    getters: {
        authenticated: state => !!state.token,
        role: state => state.user?.role
    },

    actions: {
        async login(credentials) {
            // Replaced with api.post("/login", ...)
            const response = await api.post("/login", {
                email: credentials.email,
                password: credentials.password
            });

            this.token = response.data.token;
            localStorage.setItem("token", this.token);

            await this.loadProfile();
        },

        async loadProfile() {
            // Replaced with api.get("/profile")
            const response = await api.get("/profile");
            this.user = response.data;
        },

        async logout() {
            try {
                // Replaced with await api.post("/logout")
                await api.post("/logout");
            } catch (err) {
                console.error("Logout request failed:", err);
            } finally {
                // Always clear local state even if the network request fails
                this.user = null;
                this.token = null;
                localStorage.removeItem("token");
            }
        }
    }
});
