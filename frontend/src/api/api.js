import axios from "axios";
//izmedju jednog klijenta i servera postoji jedna axios instanca
const api= axios.create({
    baseURL: "https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api",
});// onda samo na ovaj url dodajemo sta nam je potrebno

//opcioni interceptor za token
api.interceptors.request.use((config)=> {
    const token = localStorage.getItem('token');

        if(token){
            config.headers.Authorization=`Bearer ${token}`;// ako postoji token dodaje se u header httpa
        }
        return config;
});
export default api;