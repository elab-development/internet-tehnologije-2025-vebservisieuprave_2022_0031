import React, { useEffect, useState } from 'react';
import api from '../api/api';
import './MojiZahtevi.css';

// Tipovi zahteva
const zahteviTypes = [
  { value: "prebivaliste", label: "Promena prebivališta" },
  { value: "bracni_status", label: "Promena bračnog statusa" },
];

// Tipovi promene (samo ako je tip zahteva = bracni_status)
const tipPromeneOptions = [
  { value: "razvod", label: "Razvod" },
  { value: "sklapanje_braka", label: "Sklapanje braka" },
];

// Pol partnera
const partnerPolOptions = [
  { value: "M", label: "Muški" },
  { value: "Z", label: "Ženski" },
];
 const getUserFromStorage=()=>{
try {
    const raw=localStorage.getItem("user");
    return raw ? JSON.parse(raw) : null;
} catch {
    return null;
}


 }
 const user= getUserFromStorage();
 console.log("User iz localStorage:", user);
const MojiZahtevi = () => {

    const [zahtevi, setZahtevi] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");
 
// State hookovi za formu zahteva
const [tipZahteva, setTipZahteva] = useState("");   // prebivaliste / bracni_status
const [status, setStatus] = useState("kreiran");    // default vrednost
const [tipPromene, setTipPromene] = useState("");   // razvod / sklapanje_braka (ako je bracni_status)

const [imePartnera, setImePartnera] = useState("");
const [prezimePartnera, setPrezimePartnera] = useState("");
const [datumRodjenjaPartnera, setDatumRodjenjaPartnera] = useState("");
const [partnerPol, setPartnerPol] = useState("");   // M / Z

const [brojLicnogDokumenta, setBrojLicnogDokumenta] = useState("");
const [brojLicnogDokumentaPartnera, setBrojLicnogDokumentaPartnera] = useState("");
const [datumPromene, setDatumPromene] = useState("");

   const [editingId, setEditingId] = useState(null);
  

  useEffect(() => {
    loadZahtevi();
  }, []);
   const loadZahtevi= async ()=>{
    setLoading(true);
    setError("");
    try {
        const res=await api.get("/zahtev/moje");
        console.log(res.data.data);
       setZahtevi((res.data.data || []).map(z => ({
  ...z,
  id: z[" id"] || z.id
})));
    } catch (error) {
        console.error(error);
        setError("Ne mogu da ucitam zahteve.Pokusaj ponovo");

    }finally{
        setLoading(false);
    }

  };
  const handleCancelEdit = () => {
  setEditingId(null); // resetuje ID → vraća formu u stanje dodavanja
  setTipZahteva("");
  setTipPromene("");
  setImePartnera("");
  setPrezimePartnera("");
  setDatumRodjenjaPartnera("");
  setPartnerPol("");
  setBrojLicnogDokumenta("");
  setBrojLicnogDokumentaPartnera("");
  setDatumPromene("");
  setInfo("");
  setError("");
};
  const handleEdit = (zahtev) => {
 // Popuni formu sa podacima zahteva
 setEditingId(zahtev.id);
  setTipZahteva(zahtev.tip_zahteva || "");
  setTipPromene(zahtev.tip_promene || "");
  setImePartnera(zahtev.ime_partnera || "");
  setPrezimePartnera(zahtev.prezime_partnera || "");
  setDatumRodjenjaPartnera(zahtev.datum_rodjenja_partnera || "");
  setPartnerPol(zahtev.partner_pol || "");
  setBrojLicnogDokumenta(zahtev.broj_licnog_dokumenta || "");
  setBrojLicnogDokumentaPartnera(zahtev.broj_licnog_dokumenta_partnera || "");
  setDatumPromene(zahtev.datum_promene || "");

  // Zapamti ID zahteva koji se menja
setInfo("");
setError("");

};

const handleDelete = async (id) => {
   if (!window.confirm("Da li ste sigurni da želite da obrišete ovaj zahtev?")) return;
  setError("");
  setInfo("");

  try {
    const res=await api.delete(`/zahtev/${id}`, {
  headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
});
  console.log("Brisanje ID:", id);


    setZahtevi((prev) => prev.filter((z) => z.id !== id));
    setInfo("Zahtev je obrisan.");
  } catch (err) {
    console.error(err);
    setError("Greška prilikom brisanja zahteva.");
  }

};
 const handleSubmit = async (e) => {
  e.preventDefault();
  setSaving(true);
  setError("");
  setInfo("");

  if (
    !tipZahteva ||
    !tipPromene ||
    !imePartnera ||
    !prezimePartnera ||
    !datumRodjenjaPartnera ||
    !partnerPol ||
    !brojLicnogDokumenta ||
    !brojLicnogDokumentaPartnera ||
    !datumPromene
  ) {
    setError("Molimo popunite sva polja pre slanja zahteva.");
    setSaving(false);
    return;
  }

  const payload = {
    tip_zahteva: tipZahteva,
    tip_promene: tipPromene,
    ime_partnera: imePartnera,
    prezime_partnera: prezimePartnera,
    datum_rodjenja_partnera: datumRodjenjaPartnera,
    partner_pol: partnerPol,
    broj_licnog_dokumenta: brojLicnogDokumenta,
    broj_licnog_dokumenta_partnera: brojLicnogDokumentaPartnera,
    datum_promene: datumPromene,
  };
      
  try {
    let res;
    if (editingId) {
      // Ako uređuješ postojeći zahtev → PUT
      res = await api.put(`/zahtev/${editingId}`, payload, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
      });

      // Ažuriraj state
      setZahtevi((prev) =>
        prev.map((z) => (z.id === editingId ? { ...res.data, id: res.data.id || res.data[" id"] } : z))
      );
      setInfo("Zahtev uspešno izmenjen!");
      setEditingId(null);
    } else {
      // Ako dodaješ novi zahtev → POST
      res = await api.post("/zahtev", payload, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
      });

      setZahtevi((prev) => [
        ...prev,
        { ...res.data, id: res.data.id || res.data[" id"] }
      ]);
      setInfo("Zahtev uspešno sačuvan!");
    }

    // Resetuj formu
    setTipZahteva("");
    setTipPromene("");
    setImePartnera("");
    setPrezimePartnera("");
    setDatumRodjenjaPartnera("");
    setPartnerPol("");
    setBrojLicnogDokumenta("");
    setBrojLicnogDokumentaPartnera("");
    setDatumPromene("");
  } catch (err) {
    console.error(err);
    setError("Došlo je do greške prilikom čuvanja zahteva.");
  } finally {
    setSaving(false);
  }
};
   
 

  return (
     <div className="mojizahtevi-container">
      <div className="hero">
        <div className="hero-left">
          <h1>Moji zahtevi</h1>
          <p className="moto">
            Olakšaj sebi svakodnevne obaveze – podnesi zahtev online.
          </p>
          <p className="about">
            Uštedi vreme i izbegni gužve. Putem naše platforme možeš brzo i
            jednostavno da podneseš zahteve za izdavanje lične karte, promenu
            prebivališta, bračni status i druge administrativne usluge.
          </p>
         
        </div>

       <div className="zahtevi-lista">
  {zahtevi.length === 0 ? (
    <p className="no-zahtevi">Nemaš nijedan zahtev. Dodaj novi zahtev!</p>
  ) : (
    zahtevi.map((z) => {
      // Mapiranje tipa zahteva
      let tipZahtevaLabel = "";
      if (z.tip_zahteva === "bracni_status") {
        tipZahtevaLabel = "Promena bračnog statusa";
      } else if (z.tip_zahteva === "prebivaliste") {
        tipZahtevaLabel = "Promena prebivališta";
      } else {
        tipZahtevaLabel = z.tip_zahteva;
      }

      // Boja statusa
      let statusClass = "";
      if (z.status === "odobren") {
        statusClass = "status-odobren";
      } else if (z.status === "odbijen") {
        statusClass = "status-odbijen";
      } else {
        statusClass = "status-kreiran";
      }

      return (
        <div key={z.id} className="zahtev-item">
          <h3>{tipZahtevaLabel}</h3>
          {z.tip_promene && <p>Tip promene: {z.tip_promene}</p>}
          <p className={statusClass}>Status: {z.status}</p>
          <p>Datum kreiranja: {new Date(z.datum_kreiranja).toLocaleDateString()}</p>
          <div className="zahtev-actions">
      <button className="btn btn-edit" onClick={() => handleEdit(z)}>
        Uredi
      </button>
      <button className="btn btn-delete" onClick={() => handleDelete(z.id)}>
        Obriši
      </button>
    </div>

        </div>
      );
    })
  )}
</div>

    {/* NOVA SEKCIJA: Dodavanje zahteva */}
<div className="novi-zahtev-container">
 <h2>{editingId ? "Izmena zahteva" : "Dodaj zahtev"}</h2>
  <p className="novi-zahtev-text">
    Popuni formu ispod i podnesi novi zahtev brzo i jednostavno.
  </p>

  <form className="novi-zahtev-form" onSubmit={handleSubmit}>
    {/* Tip zahteva */}
    <div className="form-group">
      <label>Tip zahteva:</label>
      <select value={tipZahteva} onChange={(e) => setTipZahteva(e.target.value)} required>
        <option value="">-- Izaberi tip --</option>
        {zahteviTypes.map((t) => (
          <option key={t.value} value={t.value}>{t.label}</option>
        ))}
      </select>
    </div>

    {/* Tip promene + partner podaci */}
    {tipZahteva === "bracni_status" && (
      <>
        <div className="form-group">
          <label>Tip promene:</label>
          <select value={tipPromene} onChange={(e) => setTipPromene(e.target.value)}>
            <option value="">-- Izaberi tip promene --</option>
            {tipPromeneOptions.map((tp) => (
              <option key={tp.value} value={tp.value}>{tp.label}</option>
            ))}
          </select>
        </div>

        {/* Ime i prezime partnera pored */}
        <div className="form-row">
          <div className="form-group half">
            <label>Ime partnera:</label>
            <input type="text" value={imePartnera} onChange={(e) => setImePartnera(e.target.value)} />
          </div>
          <div className="form-group half">
            <label>Prezime partnera:</label>
            <input type="text" value={prezimePartnera} onChange={(e) => setPrezimePartnera(e.target.value)} />
          </div>
        </div>

        <div className="form-group">
          <label>Datum rođenja partnera:</label>
          <input type="date" value={datumRodjenjaPartnera} onChange={(e) => setDatumRodjenjaPartnera(e.target.value)} />
        </div>

        <div className="form-group">
          <label>Pol partnera:</label>
          <select value={partnerPol} onChange={(e) => setPartnerPol(e.target.value)}>
            <option value="">-- Izaberi pol --</option>
            {partnerPolOptions.map((p) => (
              <option key={p.value} value={p.value}>{p.label}</option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label>Broj ličnog dokumenta partnera:</label>
          <input type="text" value={brojLicnogDokumentaPartnera} onChange={(e) => setBrojLicnogDokumentaPartnera(e.target.value)} />
        </div>
      </>
    )}

    {/* Zajednička polja */}
    <div className="form-group">
      <label>Broj ličnog dokumenta:</label>
      <input type="text" value={brojLicnogDokumenta} onChange={(e) => setBrojLicnogDokumenta(e.target.value)} />
    </div>

    <div className="form-group">
      <label>Datum promene:</label>
      <input type="date" value={datumPromene} onChange={(e) => setDatumPromene(e.target.value)} />
    </div>

   <button type="submit" className="btn primary" disabled={saving}>
  {saving ? "Čuvanje..." : editingId ? "Izmeni zahtev" : "Dodaj zahtev"}
</button>

{editingId && (
  <button
    type="button"
    className="btn secondary"
    onClick={handleCancelEdit}
    style={{ marginLeft: "10px" }}
  >
    Otkaži izmenu
  </button>
)}


    {error && <p style={{ color: "red" }}>{error}</p>}
    {info && <p style={{ color: "green" }}>{info}</p>}
  </form>
</div>
        
      </div>
    </div>


  );
}


export default MojiZahtevi

