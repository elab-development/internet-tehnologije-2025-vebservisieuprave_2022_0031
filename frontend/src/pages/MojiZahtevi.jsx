import React, { useEffect, useMemo, useState } from "react";
import api from "../api/api";
import "./MojiZahtevi.css";
import useMojiZahtevi from "../hooks/useMojiZahtevi";

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

const getUserFromStorage = () => {
  try {
    const raw = localStorage.getItem("user");
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
};

const user = getUserFromStorage();
console.log("User iz localStorage:", user);

const MojiZahtevi = () => {
  const { zahtevi, setZahtevi, loading, loadError } = useMojiZahtevi();
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");

  // State hookovi za formu zahteva
  const [tipZahteva, setTipZahteva] = useState(""); // prebivaliste / bracni_status
  const [tipPromene, setTipPromene] = useState(""); // razvod / sklapanje_braka (ako je bracni_status)

  const [imePartnera, setImePartnera] = useState("");
  const [prezimePartnera, setPrezimePartnera] = useState("");
  const [datumRodjenjaPartnera, setDatumRodjenjaPartnera] = useState("");
  const [partnerPol, setPartnerPol] = useState(""); // M / Z

  const [brojLicnogDokumenta, setBrojLicnogDokumenta] = useState("");
  const [brojLicnogDokumentaPartnera, setBrojLicnogDokumentaPartnera] =
    useState("");
  const [datumPromene, setDatumPromene] = useState("");

  // ===== ADRESE (samo za prebivaliste) =====
  // Stara adresa
  const [staraUlica, setStaraUlica] = useState("");
  const [stariBroj, setStariBroj] = useState("");
  const [staroMesto, setStaroMesto] = useState("");
  const [staraOpstina, setStaraOpstina] = useState("");
  const [stariGrad, setStariGrad] = useState("");
  const [stariPostanskiBroj, setStariPostanskiBroj] = useState("");

  // Nova adresa
  const [novaUlica, setNovaUlica] = useState("");
  const [noviBroj, setNoviBroj] = useState("");
  const [novoMesto, setNovoMesto] = useState("");
  const [novaOpstina, setNovaOpstina] = useState("");
  const [noviGrad, setNoviGrad] = useState("");
  const [noviPostanskiBroj, setNoviPostanskiBroj] = useState("");

  const [editingId, setEditingId] = useState(null);

  // MAX datum za datum rođenja partnera = danas - 18 godina
  const maxPartnerDob = useMemo(() => {
    const today = new Date();
    today.setFullYear(today.getFullYear() - 18);
    return today.toISOString().split("T")[0]; // yyyy-mm-dd
  }, []);

  // Datum promene mora biti najmanje 1 dan pre danasnjeg (max = juce)
  const maxDatumPromene = useMemo(() => {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    return d.toISOString().split("T")[0]; // yyyy-mm-dd
  }, []);

  // Lokalna poruka greške samo za datum rođenja partnera (UX)
  const [partnerDobError, setPartnerDobError] = useState("");

  const validatePartnerDob = (value) => {
    if (!value) return "Datum rođenja partnera je obavezan.";

    const selected = new Date(value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const maxAllowed = new Date(maxPartnerDob);

    if (selected > today) return "Datum rođenja ne može biti u budućnosti.";
    if (selected > maxAllowed) return "Partner mora imati najmanje 18 godina.";

    return "";
  };

  // Validacija datuma promene: mora biti najkasnije juce
  const validateDatumPromene = (value) => {
    if (!value) return "Datum promene je obavezan.";

    // value i maxDatumPromene su YYYY-MM-DD -> string compare radi ispravno
    if (value > maxDatumPromene) {
      return "Datum promene mora biti najmanje 1 dan pre današnjeg datuma.";
    }
    return "";
  };

  // Kad se promeni tip zahteva, očisti partner polja ako nije bracni_status
  useEffect(() => {
    if (editingId) return;

    if (tipZahteva !== "bracni_status") {
      setTipPromene("");
      setImePartnera("");
      setPrezimePartnera("");
      setDatumRodjenjaPartnera("");
      setPartnerPol("");
      setBrojLicnogDokumentaPartnera("");
      setPartnerDobError("");
    }

    if (tipZahteva !== "prebivaliste") {
      setStaraUlica("");
      setStariBroj("");
      setStaroMesto("");
      setStaraOpstina("");
      setStariGrad("");
      setStariPostanskiBroj("");

      setNovaUlica("");
      setNoviBroj("");
      setNovoMesto("");
      setNovaOpstina("");
      setNoviGrad("");
      setNoviPostanskiBroj("");
    }
  }, [tipZahteva, editingId]);

  const resetForm = () => {
    setTipZahteva("");
    setTipPromene("");
    setImePartnera("");
    setPrezimePartnera("");
    setDatumRodjenjaPartnera("");
    setPartnerPol("");
    setBrojLicnogDokumenta("");
    setBrojLicnogDokumentaPartnera("");
    setDatumPromene("");

    setPartnerDobError("");

    // samo kada dodaješ novi zahtev
    setStaraUlica("");
    setStariBroj("");
    setStaroMesto("");
    setStaraOpstina("");
    setStariGrad("");
    setStariPostanskiBroj("");

    setNovaUlica("");
    setNoviBroj("");
    setNovoMesto("");
    setNovaOpstina("");
    setNoviGrad("");
    setNoviPostanskiBroj("");
  };

  const handleCancelEdit = () => {
    setEditingId(null);
    resetForm();
    setInfo("");
    setError("");
  };

  const handleEdit = (zahtev) => {
    setEditingId(zahtev.id);

    // Postavi tip zahteva prvo
    setTipZahteva(zahtev.tip_zahteva || "");

    // Nakon tipa, popuni adrese i partner polja
    if (zahtev.tip_zahteva === "prebivaliste") {
      setStaraUlica(zahtev.stara_adresa?.ulica || "");
      setStariBroj(zahtev.stara_adresa?.broj || "");
      setStaroMesto(zahtev.stara_adresa?.mesto || "");
      setStaraOpstina(zahtev.stara_adresa?.opstina || "");
      setStariGrad(zahtev.stara_adresa?.grad || "");
      setStariPostanskiBroj(zahtev.stara_adresa?.postanski_broj || "");

      setNovaUlica(zahtev.nova_adresa?.ulica || "");
      setNoviBroj(zahtev.nova_adresa?.broj || "");
      setNovoMesto(zahtev.nova_adresa?.mesto || "");
      setNovaOpstina(zahtev.nova_adresa?.opstina || "");
      setNoviGrad(zahtev.nova_adresa?.grad || "");
      setNoviPostanskiBroj(zahtev.nova_adresa?.postanski_broj || "");
    }

    if (zahtev.tip_zahteva === "bracni_status") {
      setTipPromene(zahtev.tip_promene || "");
      setImePartnera(zahtev.ime_partnera || "");
      setPrezimePartnera(zahtev.prezime_partnera || "");
      setDatumRodjenjaPartnera(zahtev.datum_rodjenja_partnera || "");
      setPartnerPol(zahtev.partner_pol || "");
      setBrojLicnogDokumentaPartnera(
        zahtev.broj_licnog_dokumenta_partnera || ""
      );

      // validiraj odmah (kad uđe u edit)
      setPartnerDobError(
        zahtev.datum_rodjenja_partnera
          ? validatePartnerDob(zahtev.datum_rodjenja_partnera)
          : ""
      );
    }

    setBrojLicnogDokumenta(zahtev.broj_licnog_dokumenta || "");
    setDatumPromene(zahtev.datum_promene || "");

    setInfo("");
    setError("");
  };

  const handleDelete = async (id) => {
    if (!window.confirm("Da li ste sigurni da želite da obrišete ovaj zahtev?"))
      return;

    setError("");
    setInfo("");

    try {
      await api.delete(`/zahtev/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
      });

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

    // 1) Validacija zajedničkih polja
    if (!tipZahteva || !brojLicnogDokumenta || !datumPromene) {
      setError("Molimo popunite sva polja pre slanja zahteva.");
      setSaving(false);
      return;
    }

    // Datum promene: mora biti najkasnije juce (min 1 dan pre danas)
    const dpErr = validateDatumPromene(datumPromene);
    if (dpErr) {
      setError(dpErr);
      setSaving(false);
      return;
    }

    if (tipZahteva === "prebivaliste") {
      if (
        !staraUlica ||
        !stariBroj ||
        !staroMesto ||
        !novaUlica ||
        !noviBroj ||
        !novoMesto
      ) {
        setError("Molimo popunite sva polja za staru i novu adresu.");
        setSaving(false);
        return;
      }
    }

    // 2) Dodatna validacija samo za bracni_status
    if (tipZahteva === "bracni_status") {
      if (
        !tipPromene ||
        !imePartnera ||
        !prezimePartnera ||
        !datumRodjenjaPartnera ||
        !partnerPol ||
        !brojLicnogDokumentaPartnera
      ) {
        setError("Molimo popunite sva polja za bračni status.");
        setSaving(false);
        return;
      }

      // Validacija datuma partnera (future i <18)
      const dobErr = validatePartnerDob(datumRodjenjaPartnera);
      setPartnerDobError(dobErr);

      if (dobErr) {
        setError(dobErr);
        setSaving(false);
        return;
      }
    }

    // Payload: uvek šalji zajednička polja
    const payload = {
      tip_zahteva: tipZahteva,
      broj_licnog_dokumenta: brojLicnogDokumenta,
      datum_promene: datumPromene,
    };

    if (tipZahteva === "prebivaliste") {
      payload.stara_adresa = {
        ulica: staraUlica,
        broj: stariBroj,
        mesto: staroMesto,
        opstina: staraOpstina,
        grad: stariGrad || null,
        postanski_broj: stariPostanskiBroj || null,
      };

      payload.nova_adresa = {
        ulica: novaUlica,
        broj: noviBroj,
        mesto: novoMesto,
        opstina: novaOpstina,
        grad: noviGrad || null,
        postanski_broj: noviPostanskiBroj || null,
      };
    }

    // Ako je bracni_status, dodaj i partner polja
    if (tipZahteva === "bracni_status") {
      Object.assign(payload, {
        tip_promene: tipPromene,
        ime_partnera: imePartnera,
        prezime_partnera: prezimePartnera,
        datum_rodjenja_partnera: datumRodjenjaPartnera,
        partner_pol: partnerPol,
        broj_licnog_dokumenta_partnera: brojLicnogDokumentaPartnera,
      });
    }

    try {
      let res;

      if (editingId) {
        // PUT - izmena
        res = await api.put(`/zahtev/${editingId}`, payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
        });

        // Ažuriraj state
        setZahtevi((prev) =>
          prev.map((z) =>
            z.id === editingId
              ? { ...res.data, id: res.data.id || res.data[" id"] }
              : z
          )
        );

        setInfo("Zahtev uspešno izmenjen!");
        setEditingId(null);
      } else {
        // POST - dodavanje
        res = await api.post("/zahtev", payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
        });

        setZahtevi((prev) => [
          ...prev,
          { ...res.data, id: res.data.id || res.data[" id"] },
        ]);

        setInfo("Zahtev uspešno sačuvan!");
      }

      resetForm();
    } catch (err) {
      console.error("GRESKA:", err.response?.data);
      console.error("STATUS:", err.response?.status);
      setError(
        err.response?.data?.message ||
          "Došlo je do greške prilikom čuvanja zahteva."
      );
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
          {loadError && <p style={{ color: "red" }}>{loadError}</p>}
          {loading ? (
            <p>Učitavanje...</p>
          ) : zahtevi.length === 0 ? (
            <p className="no-zahtevi">Nemaš nijedan zahtev. Dodaj novi zahtev!</p>
          ) : (
            zahtevi.map((z) => {
              let tipZahtevaLabel = "";
              if (z.tip_zahteva === "bracni_status") {
                tipZahtevaLabel = "Promena bračnog statusa";
              } else if (z.tip_zahteva === "prebivaliste") {
                tipZahtevaLabel = "Promena prebivališta";
              } else {
                tipZahtevaLabel = z.tip_zahteva;
              }

              let statusClass = "";
              if (z.status === "odobren") statusClass = "status-odobren";
              else if (z.status === "odbijen") statusClass = "status-odbijen";
              else statusClass = "status-kreiran";

              return (
                <div key={z.id} className="zahtev-item">
                  <h3>{tipZahtevaLabel}</h3>
                  {z.tip_promene && <p>Tip promene: {z.tip_promene}</p>}
                  <p className={statusClass}>Status: {z.status}</p>
                  <p>
                    Datum kreiranja:{" "}
                    {z.datum_kreiranja
                      ? new Date(z.datum_kreiranja).toLocaleDateString()
                      : "-"}
                  </p>
                  <div className="zahtev-actions">
                    <button className="btn btn-edit" onClick={() => handleEdit(z)}>
                      Uredi
                    </button>
                    <button
                      className="btn btn-delete"
                      onClick={() => handleDelete(z.id)}
                    >
                      Obriši
                    </button>
                  </div>
                </div>
              );
            })
          )}
        </div>

        {/* NOVA SEKCIJA: Dodavanje / Izmena zahteva */}
        <div className="novi-zahtev-container">
          <h2>{editingId ? "Izmena zahteva" : "Dodaj zahtev"}</h2>
          <p className="novi-zahtev-text">
            Popuni formu ispod i podnesi novi zahtev brzo i jednostavno.
          </p>

          <form className="novi-zahtev-form" onSubmit={handleSubmit}>
            {/* Tip zahteva */}
            <div className="form-group">
              <label>Tip zahteva:</label>
              <select
                value={tipZahteva}
                onChange={(e) => setTipZahteva(e.target.value)}
                required
              >
                <option value="">-- Izaberi tip --</option>
                {zahteviTypes.map((t) => (
                  <option key={t.value} value={t.value}>
                    {t.label}
                  </option>
                ))}
              </select>
            </div>

            {/* ADRESE – samo za promenu prebivališta */}
            {tipZahteva === "prebivaliste" && (
              <>
                <h3>Stara adresa</h3>

                <div className="form-group">
                  <label>Ulica</label>
                  <input
                    type="text"
                    value={staraUlica}
                    onChange={(e) => setStaraUlica(e.target.value)}
                    required
                  />
                </div>

                <div className="form-group">
                  <label>Broj</label>
                  <input
                    type="text"
                    value={stariBroj}
                    onChange={(e) => setStariBroj(e.target.value)}
                    required
                  />
                </div>

                <div className="form-group">
                  <label>Mesto</label>
                  <input
                    type="text"
                    value={staroMesto}
                    onChange={(e) => setStaroMesto(e.target.value)}
                    required
                  />
                </div>

                <div className="form-group">
                  <label>Opština</label>
                  <input
                    type="text"
                    value={staraOpstina}
                    onChange={(e) => setStaraOpstina(e.target.value)}
                    required
                  />
                </div>

                <h3>Nova adresa</h3>

                <div className="form-group">
                  <label>Ulica</label>
                  <input
                    type="text"
                    value={novaUlica}
                    onChange={(e) => setNovaUlica(e.target.value)}
                  />
                </div>

                <div className="form-group">
                  <label>Broj</label>
                  <input
                    type="text"
                    value={noviBroj}
                    onChange={(e) => setNoviBroj(e.target.value)}
                  />
                </div>

                <div className="form-group">
                  <label>Mesto</label>
                  <input
                    type="text"
                    value={novoMesto}
                    onChange={(e) => setNovoMesto(e.target.value)}
                  />
                </div>

                <div className="form-group">
                  <label>Opština</label>
                  <input
                    type="text"
                    value={novaOpstina}
                    onChange={(e) => setNovaOpstina(e.target.value)}
                    required
                  />
                </div>
              </>
            )}

            {/* Tip promene + partner podaci */}
            {tipZahteva === "bracni_status" && (
              <>
                <div className="form-group">
                  <label>Tip promene:</label>
                  <select
                    value={tipPromene}
                    onChange={(e) => setTipPromene(e.target.value)}
                  >
                    <option value="">-- Izaberi tip promene --</option>
                    {tipPromeneOptions.map((tp) => (
                      <option key={tp.value} value={tp.value}>
                        {tp.label}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-row">
                  <div className="form-group half">
                    <label>Ime partnera:</label>
                    <input
                      type="text"
                      value={imePartnera}
                      onChange={(e) => setImePartnera(e.target.value)}
                    />
                  </div>
                  <div className="form-group half">
                    <label>Prezime partnera:</label>
                    <input
                      type="text"
                      value={prezimePartnera}
                      onChange={(e) => setPrezimePartnera(e.target.value)}
                    />
                  </div>
                </div>

                <div className="form-group">
                  <label>Datum rođenja partnera:</label>
                  <input
                    type="date"
                    value={datumRodjenjaPartnera}
                    onChange={(e) => {
                      const val = e.target.value;
                      setDatumRodjenjaPartnera(val);
                      setPartnerDobError(validatePartnerDob(val));
                    }}
                    max={maxPartnerDob}
                    required
                  />
                  {partnerDobError && (
                    <p style={{ color: "red", marginTop: 6 }}>
                      {partnerDobError}
                    </p>
                  )}
                </div>

                <div className="form-group">
                  <label>Pol partnera:</label>
                  <select
                    value={partnerPol}
                    onChange={(e) => setPartnerPol(e.target.value)}
                  >
                    <option value="">-- Izaberi pol --</option>
                    {partnerPolOptions.map((p) => (
                      <option key={p.value} value={p.value}>
                        {p.label}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-group">
                  <label>Broj ličnog dokumenta partnera:</label>
                  <input
                    type="text"
                    value={brojLicnogDokumentaPartnera}
                    onChange={(e) =>
                      setBrojLicnogDokumentaPartnera(e.target.value)
                    }
                  />
                </div>
              </>
            )}

            {/* Zajednička polja */}
            <div className="form-group">
              <label>Broj ličnog dokumenta:</label>
              <input
                type="text"
                value={brojLicnogDokumenta}
                onChange={(e) => setBrojLicnogDokumenta(e.target.value)}
              />
            </div>

            <div className="form-group">
              <label>Datum promene:</label>
              <input
                type="date"
                value={datumPromene}
                onChange={(e) => setDatumPromene(e.target.value)}
                max={maxDatumPromene} // najkasnije juce
                required
              />
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
};

export default MojiZahtevi;




