//stavljamo da bude js jer nam samo vraca niz zahteva, nema html dela
//svaki put kada pozovem ovu kuku ona ce procitati zahteve iz baze

import { useEffect, useState } from "react";
import api from "../api/api";

// korisnički definisana kuka (custom hook)
const useMojiZahtevi = () => {
  const [zahtevi, setZahtevi] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState("");

  const loadZahtevi = async () => {
    setLoading(true);
    setLoadError("");
    try {
      const res = await api.get("/zahtev/moje");
      setZahtevi(
        (res.data.data || []).map((z) => ({
          ...z,
          id: z[" id"] || z.id,
        }))
      );
    } catch (error) {
      console.error(error);
      setLoadError("Ne mogu da učitam zahteve. Pokušaj ponovo");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadZahtevi();
  }, []);
//kuka vraca niz, seter za niz, loading (da li ih ucitavam trenutno ili su vec ucitani)
//loadZahtevi - samu fju da bi mogli kasnije da je pozivamo
  return {
    zahtevi,
    setZahtevi,
    loading,
    loadError,
    loadZahtevi,
  };
};

export default useMojiZahtevi;
