import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import ZahtevCard from "../components/ZahtevCard";
import "./UnprocessedRequestsPage.css";

const UnprocessedRequestsPage = () => {
  const navigate = useNavigate();
  const [zahtevi, setZahtevi] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [filter, setFilter] = useState("svi"); // novi state za filter

  // funkcija koja se poziva na klik kartice
  const handleClick = (id) => {
    navigate(`/admin/request/${id}`);
  };

  // funkcija za fetch zahteva sa opcionalnim filterom
  const fetchZahtevi = async (tip = "") => {
    setLoading(true);
    setError("");
    try {
      const token = localStorage.getItem("token");
      let url = "https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api/admin/neobradjeniZahtevi";
      if (tip && tip !== "svi") {
        url += `?tip_zahteva=${tip}`;
      }
      const res = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
          Authorization: "Bearer " + token,
        },
      });

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

  useEffect(() => {
    fetchZahtevi();
  }, []);

  // poziva se kada se promeni filter
  const handleFilterChange = (e) => {
    const tip = e.target.value;
    setFilter(tip);
    fetchZahtevi(tip);
  };

  if (loading) return <div className="loading">Učitavanje zahteva...</div>;
  if (error) return <div className="error">{error}</div>;
  if (zahtevi.length === 0) return <div>Nema neobrađenih zahteva.</div>;

  return (
    <div className="neobradjeni-zahtevi-page">
      <h1>Neobrađeni zahtevi</h1>

      {/* Dropdown za filter po tipu */}
      <div className="filter-container">
        <label htmlFor="filter">Filter po tipu:</label>
        <select id="filter" value={filter} onChange={handleFilterChange}>
          <option value="svi">Svi</option>
          <option value="prebivaliste">Promena prebivališta</option>
          <option value="bracni_status">Promena bračnog statusa</option>
        </select>
      </div>

      <div className="zahtevi-grid">
        {zahtevi.map((z) => (
          <ZahtevCard key={z.id} zahtev={z} onClick={handleClick} />
        ))}
      </div>
    </div>
  );
};

export default UnprocessedRequestsPage;