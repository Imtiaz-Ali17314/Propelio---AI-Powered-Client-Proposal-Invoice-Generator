import axios from "axios";

const api = axios.create({
    baseURL: "/", // same domain, so relative paths work
    withCredentials: true, // send cookies (session + CSRF) with every request
    withXSRFToken: true, // axios reads XSRF-TOKEN cookie and sends X-XSRF-TOKEN header automatically
    headers: {
        Accept: "application/json",
    },
});

export default api;
