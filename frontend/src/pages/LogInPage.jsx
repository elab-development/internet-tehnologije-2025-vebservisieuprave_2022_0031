import React, { useState } from 'react';
import './LogInPage.css'
import api from '../api/api';


export const LogInPage = () => {

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleSubmit= async (e) =>{
    e.preventDefault();// kako bismo sprecili da nam svaki sekund refresh stranice
    //await- ceka da dobijemo kompletan odgovor ne prelazi na naredne linije koda
    const res= await api.post('/login', {email, password})
    const {token, user, message}=res.data;


    console.log('Uspesna prijava:', message, token, user);


  
  }


  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1 className="auth-title">Prijavi se na mojaEUprava portal</h1>
        <p className="auth-subtitle">
          Dobrodošli! Molimo unesite svoj email i lozinku kako biste pristupili svom nalogu.
        </p>

        <form  className="auth-form" onSubmit={handleSubmit}>
          

          <div className="auth-field">
            <label htmlFor="email">Email adresa:</label>
            <input
              id="email"
              type="email"
              placeholder="ime.prezime@example.com"
              value={email}// ovim su povezana nasa polja i promenljive
              onChange={(e) => setEmail(e.target.value)}
              autoComplete="email"
              required
            />
          </div>

          <div className="auth-field">
            <label htmlFor="password">Lozinka:</label>
            <input
              id="password"
              type="password"
              placeholder="Unesite lozinku"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
              required
            />
          </div>

          <button
           type="submit"
           className="btn-primary auth-submit"
           >
          
          {"Prijavi se"}
          </button>
          <div className="auth-extra">
            <span>Zaboravili ste lozinku?</span>
            <span className="auth-extra-link">
              Obratite se opciji za reset lozinke.
            </span>
          </div>

        </form>
      </div>
    </div>
  );
};

export default LogInPage;