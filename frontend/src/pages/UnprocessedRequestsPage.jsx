import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom"; // dodaj ovo
import ZahtevCard from "../components/ZahtevCard";
import "./UnprocessedRequestsPage.css";

const UnprocessedRequestsPage = () => {
  const navigate = useNavigate(); // hook za navigaciju
  const [zahtevi, setZahtevi] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  // funkcija koja se poziva na klik kartice
  const handleClick = (id) => {
    // navigacija na stranicu detalja zahteva
    navigate(`/admin/request/${id}`);
  };

  useEffect(() => {
    const fetchZahtevi = async () => {
      setLoading(true);
      setError("");
      try {
        const token = localStorage.getItem("token");
        const res = await fetch(
          "http://localhost:8000/api/admin/neobradjeniZahtevi",
          {
            headers: {
              "Content-Type": "application/json",
              Authorization: "Bearer " + token,
            },
          }
        );

        if (!res.ok) {
          throw new Error("Greška pri učitavanju zahteva");
        }

        const data = await res.json();
        setZahtevi(data);
      } catch (err) {
        setError(err.message || "Došlo je do greške");
      } finally {
        setLoading(false);
      }
    };

    fetchZahtevi();
  }, []);

  if (loading) return <div className="loading">Učitavanje zahteva...</div>;
  if (error) return <div className="error">{error}</div>;
  if (zahtevi.length === 0) return <div>Nema neobrađenih zahteva.</div>;

  return (
    <div className="neobradjeni-zahtevi-page">
      <h1>Neobrađeni zahtevi</h1>
      <div className="zahtevi-grid">
        {zahtevi.map((z) => (
          <ZahtevCard key={z.id} zahtev={z} onClick={handleClick} />
        ))}
      </div>
    </div>
  );
};

export default UnprocessedRequestsPage;