import api from "./axios";

export const getListings = () => {
  return api.get("/listings");
};