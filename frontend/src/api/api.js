import axios from "axios";
//izmedju jednog klijenta i servera postoji jedna axios instanca
const api= axios.create({
    baseURL: "http://127.0.0.1:8000/api",// onda samo na ovaj url dodajemo sta nam je potrebno
});

api.interceptors.request.use((config)=> {
    const token =
        sessionStorage.getItem('token') || localStorage.getItem('token');

        if(token){
            config.headers.Authorization=`Bearer ${token}`;
        }
        return config;
});
export default api;