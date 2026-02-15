import React, { useMemo, useState } from "react";
import api from "../api/api";
import "./ZakaziTerminForm.css";

const ZakaziTerminForm = ({ user }) => {
  const [tipDokumenta, setTipDokumenta] = useState("licna_karta");
  const [lokacija, setLokacija] = useState("");
  const [datumVreme, setDatumVreme] = useState("");
  const [info, setInfo] = useState("");
  const [error, setError] = useState("");

  //Minimalno dozvoljeno vreme = sada + 30 minuta
  const minDateTime = useMemo(() => {
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30);

    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, "0");
    const dd = String(now.getDate()).padStart(2, "0");
    const hh = String(now.getHours()).padStart(2, "0");
    const min = String(now.getMinutes()).padStart(2, "0");

    return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
  }, []);

  const validateDatumVreme = (value) => {
    if (!value) return "Datum i vreme su obavezni.";

    const selected = new Date(value);
    const minAllowed = new Date();
    minAllowed.setMinutes(minAllowed.getMinutes() + 30);

    if (isNaN(selected.getTime()))
      return "Neispravan format datuma i vremena.";

    if (selected < minAllowed)
      return "Termin mora biti zakazan najmanje 30 minuta unapred.";

    return "";
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setInfo("");

    const validationError = validateDatumVreme(datumVreme);
    if (validationError) {
      setError(validationError);
      return;
    }

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
      setLokacija("");
      setDatumVreme("");
      setError("");
    } catch (err) {
      setInfo("");

      if (err.response?.status === 409) {
        setError("Termin je zauzet, izaberite drugi.");
      } else if (err.response?.status === 422) {
        setError("Validacija nije prošla. Proverite unete podatke.");
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
        <select
          value={tipDokumenta}
          onChange={(e) => setTipDokumenta(e.target.value)}
        >
          <option value="licna_karta">Lična karta</option>
          <option value="pasos">Pasoš</option>
        </select>

        <label>Lokacija:</label>
        <input
          type="text"
          value={lokacija}
          onChange={(e) => setLokacija(e.target.value)}
          required
        />

        <label>Datum i vreme:</label>
        <input
          type="datetime-local"
          value={datumVreme}
          min={minDateTime} // ne dozvoljava pre 30 min
          onChange={(e) => {
            const val = e.target.value;
            setDatumVreme(val);

            const msg = validateDatumVreme(val);
            if (msg) {
              setError(msg);
              setInfo("");
            } else {
              setError("");
            }
          }}
          required
        />

        <div style={{ marginTop: "10px" }}>
          <button type="submit">Zakaži</button>
          <button
            type="button"
            onClick={() => {
              setLokacija("");
              setDatumVreme("");
              setError("");
              setInfo("");
            }}
          >
            Otkaži
          </button>
        </div>
      </form>

      {info && <p className="success-message">{info}</p>}
      {error && <p className="error-message">{error}</p>}
    </div>
  );
};

export default ZakaziTerminForm;
