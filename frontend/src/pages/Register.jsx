import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/api";
import "./LogInPage.css"; // koristimo isti css kao za login
import TextInput from "../components/TextInput";
import FileInput from "../components/FileInput";
import PrimaryButton from "../components/PrimaryButton";

const Register = () => {
  const navigate = useNavigate();

  const [ime, setIme] = useState("");
  const [prezime, setPrezime] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [slika, setSlika] = useState(null);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");

  const handleFileChange = (e) => {
    const file = e.target.files?.[0];
    setSlika(file || null);
    setError(""); // UX: briši grešku kad korisnik menja fajl
  };

  // UX: briši grešku čim korisnik krene ponovo da kuca
  const handleImeChange = (e) => {
    setIme(e.target.value);
    setError("");
  };
  const handlePrezimeChange = (e) => {
    setPrezime(e.target.value);
    setError("");
  };
  const handleEmailChange = (e) => {
    setEmail(e.target.value);
    setError("");
  };
  const handlePasswordChange = (e) => {
    setPassword(e.target.value);
    setError("");
  };
  const handlePasswordConfirmationChange = (e) => {
    setPasswordConfirmation(e.target.value);
    setError("");
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
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

      if (slika) {
        formData.append("slika", slika);
      }

      const res = await api.post("/register", formData);
      const { message } = res.data;

      setInfo(
        message || "Registracija uspesna. Proverite email i verifikujte nalog."
      );

      setLoading(false);

      setTimeout(() => {
        navigate("/login");
      }, 1200);
    } catch (err) {
      console.error(err);
      setLoading(false);

      if (err.response) {
        if (err.response.status === 422) {
          const errors = err.response.data?.errors || {};
          const firstKey = Object.keys(errors)[0];
          const firstMsg = firstKey ? errors[firstKey][0] : null;

          setError(
            firstMsg || "Validacija nije prosla. Proverite uneta polja"
          );
        } else {
          setError("Došlo je do greške. Pokušajte ponovo.");
        }
      } else {
        setError("Server nije dostupan. Proverite konekciju.");
      }
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1 className="auth-title">Registruj se na mojaEUprava portal</h1>
        <p className="auth-subtitle">
          Popunite sledeća polja kako biste se registrovali i započeli korišćenje
          portala mojaEUprava.
        </p>

        <form className="auth-form" onSubmit={handleSubmit}>
          <TextInput
            id="ime"
            label="Ime:"
            placeholder="Unesite ime"
            value={ime}
            onChange={handleImeChange}
            required
          />

          <TextInput
            id="prezime"
            label="Prezime:"
            placeholder="Unesite prezime"
            value={prezime}
            onChange={handlePrezimeChange}
            required
          />

          <TextInput
            id="email"
            label="Email adresa:"
            type="email"
            placeholder="ime.prezime@example.com"
            value={email}
            onChange={handleEmailChange}
            autoComplete="email"
            required
          />

          <TextInput
            id="password"
            label="Lozinka:"
            type="password"
            placeholder="Unesite lozinku (min 6 karaktera)"
            value={password}
            onChange={handlePasswordChange}
            autoComplete="new-password"
            showPasswordToggle={true}
            required
          />

          <TextInput
            id="password_confirmation"
            label="Potvrda lozinke:"
            type="password"
            placeholder="Ponovo unesite lozinku"
            value={passwordConfirmation}
            onChange={handlePasswordConfirmationChange}
            autoComplete="new-password"
            showPasswordToggle={true}
            required
          />

          <FileInput
            id="slika"
            label="Profilna slika (opciono)"
            accept="image/png,image/jpeg,image/jpg"
            onChange={handleFileChange}
            hint="Dozvoljeni formati: JPG, JPEG, PNG. Maksimalno 2MB."
          />

          {/* PORUKE - koriste novi CSS */}
          {info && <p className="auth-message success">{info}</p>}
          {error && <p className="auth-message error">{error}</p>}

          <PrimaryButton type="submit" loading={loading} loadingText="Registracija...">
            Registruj se
          </PrimaryButton>
        </form>
      </div>
    </div>
  );
};

export default Register;
