import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/api";
import TextInput from "../components/TextInput";
import FileInput from "../components/FileInput";
import PrimaryButton from "../components/PrimaryButton";
import "./RegisterPage.css";

const RegisterPage = () => {
  const navigate = useNavigate();

  const [korisnikTip, setKorisnikTip] = useState(""); // 'domaci' ili 'strani'
  const [step, setStep] = useState(1); // korak za domace

  // Za domaće
  const [jmbg, setJmbg] = useState("");
  const [drzavljanin, setDrzavljanin] = useState(null);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");

  // Za strane
  const [ime, setIme] = useState("");
  const [prezime, setPrezime] = useState("");
  const [brojPasosa, setBrojPasosa] = useState("");
  const [drzavljanstvo, setDrzavljanstvo] = useState("");
  const [slika, setSlika] = useState(null);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");

  // ----------------- DOMAĆI KORISNIK -----------------
  const handleCheckJmbg = async () => {
    setError("");
    setLoading(true);
    try {
      const res = await api.post("/check-jmbg", { jmbg });
      setDrzavljanin(res.data.drzavljanin);
      setStep(2);
    } catch (err) {
      setError(err.response?.data?.message || "Greška pri proveri JMBG-a");
    } finally {
      setLoading(false);
    }
  };

  const handleRegisterDomaci = async () => {
    setError("");
    setInfo("");
    setLoading(true);
    try {
      const res = await api.post("/register-domaci", {
        jmbg,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      setInfo(res.data.message);
      setTimeout(() => navigate("/login"), 1200);
    } catch (err) {
      setError(err.response?.data?.message || "Greška pri registraciji");
    } finally {
      setLoading(false);
    }
  };

  // ----------------- STRANI KORISNIK -----------------
  const handleRegisterStrani = async () => {
    setError("");
    setInfo("");
    setLoading(true);

    try {
      const formData = new FormData();
      formData.append("ime", ime);
      formData.append("prezime", prezime);
      formData.append("email", email);
      formData.append("password", password);
      formData.append("password_confirmation", passwordConfirmation);
      formData.append("broj_pasosa", brojPasosa);
      formData.append("drzavljanstvo", drzavljanstvo);
      if (slika) formData.append("slika", slika);

      const res = await api.post("/register-strani", formData);
      setInfo(res.data.message);
      setTimeout(() => navigate("/login"), 1200);
    } catch (err) {
      setError(err.response?.data?.message || "Greška pri registraciji");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      {!korisnikTip && (
        <div className="auth-card">
          <h1 className="auth-title">Registrujte se na mojaEUprava</h1>
          <p>Izaberite tip korisnika:</p>
          <div className="button-group">
            <PrimaryButton onClick={() => setKorisnikTip("domaci")}>
              Domaći državljanin
            </PrimaryButton>
            <PrimaryButton onClick={() => setKorisnikTip("strani")}>
              Strani državljanin
            </PrimaryButton>
          </div>
        </div>
      )}

      {korisnikTip === "domaci" && (
        <div className="auth-card">
          <h1 className="auth-title">Registracija domaćeg državljanina</h1>

          {step === 1 && (
            <>
              <TextInput
                label="Unesite JMBG"
                value={jmbg}
                onChange={(e) => setJmbg(e.target.value)}
              />
              <PrimaryButton onClick={handleCheckJmbg} loading={loading}>
                Proveri JMBG
              </PrimaryButton>
            </>
          )}

          {step === 2 && drzavljanin && (
            <>
              <p>
                Registracija za: {drzavljanin.ime} {drzavljanin.prezime}
              </p>
              <TextInput
                label="Email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
              <TextInput
                label="Šifra"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
              <TextInput
                label="Potvrda šifre"
                type="password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
              />
              <PrimaryButton onClick={handleRegisterDomaci} loading={loading}>
                Registruj se
              </PrimaryButton>
            </>
          )}

          {info && <p className="auth-message success">{info}</p>}
          {error && <p className="auth-message error">{error}</p>}
        </div>
      )}

      {korisnikTip === "strani" && (
        <div className="auth-card">
          <h1 className="auth-title">Registracija stranog državljanina</h1>
          <TextInput
            label="Ime"
            value={ime}
            onChange={(e) => setIme(e.target.value)}
          />
          <TextInput
            label="Prezime"
            value={prezime}
            onChange={(e) => setPrezime(e.target.value)}
          />
          <TextInput
            label="Email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
          <TextInput
            label="Šifra"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
          <TextInput
            label="Potvrda šifre"
            type="password"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
          />
           <TextInput
           label="Broj pasoša"
           value={brojPasosa}
          onChange={(e) => setBrojPasosa(e.target.value)}
          />
         <TextInput
         label="Državljanstvo"
         value={drzavljanstvo}
         onChange={(e) => setDrzavljanstvo(e.target.value)}
        />
          <FileInput
            label="Profilna slika (opciono)"
            onChange={(e) => setSlika(e.target.files?.[0] || null)}
          />
          <PrimaryButton onClick={handleRegisterStrani} loading={loading}>
            Registruj se
          </PrimaryButton>

          {info && <p className="auth-message success">{info}</p>}
          {error && <p className="auth-message error">{error}</p>}
        </div>
      )}
    </div>
  );
};

export default RegisterPage;
