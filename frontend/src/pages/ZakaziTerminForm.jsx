import React, { useState } from "react";
import api from "../api/api";
import "./ZakaziTerminForm.css"; // 

const ZakaziTerminForm = ({ user }) => {
  const [tipDokumenta, setTipDokumenta] = useState("licna_karta");
  const [lokacija, setLokacija] = useState("");
  const [datumVreme, setDatumVreme] = useState("");
  const [info, setInfo] = useState("");
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setInfo("");

    try {
      const payload = {
        tip_dokumenta: tipDokumenta,
        lokacija,
        datum_vreme: datumVreme,
        korisnik_id: user?.id,
      };

      await api.post("/termin", payload, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
      });

      setInfo("Termin uspešno zakazan!");
      setError("");
    } catch (err) {
      setInfo("");
      if (err.response?.status === 409) {
        setError("Termin je zauzet, izaberite drugi.");
      } else {
        setError("Greška prilikom zakazivanja termina.");
      }
    }
  };

  return (
    <div className="zakazi-termin-container">
      <h3>Zakaži termin</h3>
      <form onSubmit={handleSubmit}>
        <label>Tip dokumenta:</label>
        <select value={tipDokumenta} onChange={(e) => setTipDokumenta(e.target.value)}>
          <option value="licna_karta">Lična karta</option>
          <option value="pasos">Pasoš</option>
        </select>

        <label>Lokacija:</label>
        <input type="text" value={lokacija} onChange={(e) => setLokacija(e.target.value)} required />

        <label>Datum i vreme:</label>
        <input type="datetime-local" value={datumVreme} onChange={(e) => setDatumVreme(e.target.value)} required />

        <div style={{ marginTop: "10px" }}>
          <button type="submit">Zakaži</button>
          <button type="button" onClick={() => { setLokacija(""); setDatumVreme(""); }}>Otkaži</button>
        </div>
      </form>

      {info && <p className="success-message">{info}</p>}
      {error && <p className="error-message">{error}</p>}
    </div>
  );
};

export default ZakaziTerminForm;