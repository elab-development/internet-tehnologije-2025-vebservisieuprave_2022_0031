import React, { useState } from "react";
import "./LogInPage.css";
import api from "../api/api";
import { useNavigate } from "react-router-dom";
import TextInput from "../components/TextInput";
import PrimaryButton from "../components/PrimaryButton";

export const LogInPage = ({ setUser }) => {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    setInfo("");

    try {
      const res = await api.post("/login", { email, password });
      const { token, user, message } = res.data;

      if (token) {
        localStorage.setItem("token", token);
      }

      if (user) {
        localStorage.setItem("user", JSON.stringify(user));
        setUser(user);
      }

      console.log("Login response:", res.data);
      setInfo(message || "Uspešno ste prijavljeni.");

      setTimeout(() => {
        navigate("/userpage");
      }, 800);
    } catch (err) {
      console.error("LOGIN ERROR:", err);

      if (err.response?.status === 401) {
        setError("Neispravna email adresa ili lozinka.");
      } else if (err.response?.status === 422) {
        setError("Molimo popunite ispravno sva polja.");
      } else {
        setError("Došlo je do greške. Pokušajte ponovo.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1 className="auth-title">Prijavi se na mojaEUprava portal</h1>
        <p className="auth-subtitle">
          Dobrodošli! Molimo unesite svoj email i lozinku kako biste pristupili
          svom nalogu.
        </p>

        <form className="auth-form" onSubmit={handleSubmit}>
          <TextInput
            id="email"
            label="Email adresa:"
            type="email"
            placeholder="ime.prezime@example.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            autoComplete="email"
            required
          />

          <TextInput
            id="password"
            label="Lozinka:"
            type="password"
            placeholder="Unesite lozinku"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            autoComplete="current-password"
            showPasswordToggle={true}
            required
          />

          {info && <p className="auth-message success">{info}</p>}

          {error && <p className="auth-message error">{error}</p>}

          <PrimaryButton
            type="submit"
            loading={loading}
            loadingText="Prijavljivanje..."
          >
            Prijavi se
          </PrimaryButton>

          
        </form>
      </div>
    </div>
  );
};

export default LogInPage;