import axios from "axios";

const api = axios.create({
  baseURL: "https://pinedalawoffice.emberwebsolutions.com/api",
  withCredentials: true,
  withXSRFToken: true,

  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

export default api;
