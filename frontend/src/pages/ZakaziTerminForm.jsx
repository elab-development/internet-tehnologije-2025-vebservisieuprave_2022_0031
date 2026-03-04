import React, { useEffect, useMemo, useState } from "react";
import Select from "react-select";
import api from "../api/api";
import "./ZakaziTerminForm.css";

const GRADSKE_OPSTINE = {
  Beograd: [
    "Barajevo",
    "Čukarica",
    "Grocka",
    "Lazarevac",
    "Mladenovac",
    "Novi Beograd",
    "Obrenovac",
    "Palilula",
    "Rakovica",
    "Savski venac",
    "Sopot",
    "Stari grad",
    "Surčin",
    "Voždovac",
    "Vračar",
    "Zemun",
    "Zvezdara",
  ],
  "Novi Sad": ["Novi Sad", "Petrovaradin"],
  Niš: ["Medijana", "Palilula", "Crveni krst", "Pantelej", "Niška Banja"],
  Požarevac: ["Požarevac", "Kostolac"],
  Vranje: ["Vranje", "Vranjska Banja"],
};

const normalize = (s) =>
  (s || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/\p{Diacritic}/gu, "")
    .trim();

const canonicalCity = (name) => {
  const raw = String(name || "").trim();
  const n = normalize(raw);

  if (n === "belgrade") return "Beograd";
  if (n === "nis") return "Niš";
  if (n === "pozarevac") return "Požarevac";
  if (n === "novi belgrade") return "Novi Beograd";

  // District -> Distrikt
  if (/\bdistrict\b/.test(n)) return raw.replace(/\bDistrict\b/gi, "Distrikt");

  return raw;
};

const OPSTINE_SET = new Set(
  Object.values(GRADSKE_OPSTINE)
    .flat()
    .map((o) => normalize(o))
);

const hasDiacritics = (s) => /[čćšđžČĆŠĐŽ]/.test(s || "");

const dedupeByNormalized = (arr) => {
  const map = new Map();
  for (const raw of arr) {
    const name = String(raw || "").trim();
    if (!name) continue;
    const key = normalize(name);
    const existing = map.get(key);
    if (!existing) map.set(key, name);
    else if (!hasDiacritics(existing) && hasDiacritics(name)) map.set(key, name);
  }
  return Array.from(map.values());
};

const shouldExcludeFromCityList = (name) => {
  const n = normalize(name);

  // izbaci opštine (Beogradove, Niške, NS, Vranje...) da ne budu "grad"
  if (OPSTINE_SET.has(n)) return true;

  // izbaci sve varijacije Mladenovca
  if (n.includes("mladenovac")) return true;

  // izbaci Zemun Polje i sve što sadrži "zemun" a nije baš "zemun"
  if (n.includes("zemun") && n !== "zemun") return true;

  // izbaci varijacije "Novi Beograd ..."
  if (n.includes("novi beograd") && n !== "novi beograd") return true;

  return false;
};

// Gradovi koji moraju da postoje u listi (da bi se pojavile opštine)
const GRADOVI_SA_OPSTINAMA = Object.keys(GRADSKE_OPSTINE);

const ZakaziTerminForm = ({ user }) => {
  const [tipDokumenta, setTipDokumenta] = useState("licna_karta");

  const [grad, setGrad] = useState("");
  const [opstina, setOpstina] = useState("");
  const [lokacija, setLokacija] = useState("");

  const [mestaIzApi, setMestaIzApi] = useState([]);
  const [loadingLokacije, setLoadingLokacije] = useState(false);
  const [lokacijeError, setLokacijeError] = useState("");

  const [datumVreme, setDatumVreme] = useState("");
  const [info, setInfo] = useState("");
  const [error, setError] = useState("");

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

    if (isNaN(selected.getTime())) return "Neispravan format datuma i vremena.";
    if (selected < minAllowed)
      return "Termin mora biti zakazan najmanje 30 minuta unapred.";

    return "";
  };

  useEffect(() => {
    const loadLokacije = async () => {
      setLoadingLokacije(true);
      setLokacijeError("");

      try {
        const res = await fetch(
          "https://countriesnow.space/api/v0.1/countries/cities",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ country: "Serbia" }),
          }
        );

        const json = await res.json();
        if (!json?.data || !Array.isArray(json.data)) {
          throw new Error("API nije vratio listu gradova/mesta.");
        }

        const names = json.data
          .filter(Boolean)
          .map((x) => String(x).trim())
          .map(canonicalCity);

        const filtered = names.filter((name) => !shouldExcludeFromCityList(name));

        let unique = dedupeByNormalized(filtered);

        // garancija da ovi gradovi uvek postoje u dropdown-u
        const setNorm = new Set(unique.map((x) => normalize(x)));
        for (const g of GRADOVI_SA_OPSTINAMA) {
          if (!setNorm.has(normalize(g))) unique.push(g);
        }

        unique = dedupeByNormalized(unique).sort((a, b) => a.localeCompare(b, "sr"));

        setMestaIzApi(unique);
      } catch (e) {
        console.error(e);
        setLokacijeError(
          "Ne mogu da učitam lokacije iz API-ja. Možete uneti lokaciju ručno."
        );
      } finally {
        setLoadingLokacije(false);
      }
    };

    loadLokacije();
  }, []);

  const gradOptions = useMemo(
    () => mestaIzApi.map((g) => ({ value: g, label: g })),
    [mestaIzApi]
  );

  const selectedGrad = useMemo(
    () => (grad ? { value: grad, label: grad } : null),
    [grad]
  );

  const opstineZaGrad = useMemo(() => GRADSKE_OPSTINE[grad] || null, [grad]);

  const opstinaOptions = useMemo(() => {
    if (!opstineZaGrad) return [];
    return opstineZaGrad.map((o) => ({ value: o, label: o }));
  }, [opstineZaGrad]);

  const selectedOpstina = useMemo(
    () => (opstina ? { value: opstina, label: opstina } : null),
    [opstina]
  );

  useEffect(() => {
    if (!grad) {
      setOpstina("");
      setLokacija("");
      return;
    }

    if (!GRADSKE_OPSTINE[grad]) {
      setOpstina("");
      setLokacija(grad);
    } else {
      setOpstina("");
      setLokacija("");
    }
  }, [grad]);

  useEffect(() => {
    if (!grad) return;

    if (GRADSKE_OPSTINE[grad]) {
      if (opstina) setLokacija(`${grad} - ${opstina}`);
      else setLokacija("");
    }
  }, [grad, opstina]);

  const validateLokacija = () => {
    if (lokacijeError) {
      if (!lokacija.trim()) return "Lokacija je obavezna.";
      return "";
    }

    if (!grad) return "Izaberite mesto/grad.";
    if (GRADSKE_OPSTINE[grad] && !opstina) return "Izaberite opštinu.";
    if (!lokacija) return "Lokacija je obavezna.";
    return "";
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setInfo("");

    const lokErr = validateLokacija();
    if (lokErr) {
      setError(lokErr);
      return;
    }

    const dtErr = validateDatumVreme(datumVreme);
    if (dtErr) {
      setError(dtErr);
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
      setGrad("");
      setOpstina("");
      setLokacija("");
      setDatumVreme("");
      setError("");
    } catch (err) {
      setInfo("");
      setError("Greška prilikom zakazivanja termina.");
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
          <option value="pasos">Pasoš</option>
          <option value="licna_karta">Lična karta</option>
        </select>

        <label>Lokacija (mesto/grad):</label>

        {lokacijeError ? (
          <>
            <input
              type="text"
              value={lokacija}
              onChange={(e) => setLokacija(e.target.value)}
              placeholder="Unesite lokaciju (npr. Beograd - Vračar)"
              required
            />
            <p className="error-message" style={{ marginTop: 6 }}>
              {lokacijeError}
            </p>
          </>
        ) : (
          <>
            <Select
              isLoading={loadingLokacije}
              options={gradOptions}
              value={selectedGrad}
              onChange={(opt) => setGrad(opt?.value ?? "")}
              placeholder={
                loadingLokacije ? "Učitavanje..." : "Pretraži i izaberi mesto..."
              }
              isSearchable
            />

            {opstineZaGrad && (
              <>
                <label style={{ marginTop: 10 }}>Opština:</label>
                <Select
                  options={opstinaOptions}
                  value={selectedOpstina}
                  onChange={(opt) => setOpstina(opt?.value ?? "")}
                  placeholder="Pretraži i izaberi opštinu..."
                  isSearchable
                />
              </>
            )}
          </>
        )}

        <label>Datum i vreme:</label>
        <input
          type="datetime-local"
          value={datumVreme}
          min={minDateTime}
          onChange={(e) => setDatumVreme(e.target.value)}
          required
        />

        <div style={{ marginTop: "10px" }}>
          <button type="submit">Zakaži</button>
          <button
            type="button"
            onClick={() => {
              setGrad("");
              setOpstina("");
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
