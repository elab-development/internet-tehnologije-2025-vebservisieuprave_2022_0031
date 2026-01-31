import React, { useState } from 'react';
import './LogInPage.css'
import api from '../api/api';
import { useNavigate } from 'react-router-dom';


export const LogInPage = () => {
  const navigate = useNavigate(); //navigaciona ruta
  const [email, setEmail] = useState("nitzsche.elmo@example.net");
  const [password, setPassword] = useState("password");

  const [loading, setLoading] = useState(false);// jer kada otvorimo stranicu forma ne radi nista pa je loading false
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");



  const handleSubmit= async (e) =>{
    e.preventDefault();// kako bismo sprecili da nam svaki sekund refresh stranice
    setLoading(true);
    setError("");
    setInfo("");
    
    try{//await- ceka da dobijemo kompletan odgovor ne prelazi na naredne linije koda
      const res= await api.post('/login', {email, password})
      const {token, user, message}=res.data;
      
      localStorage.setItem("token", token);
      localStorage.setItem("usrer", JSON.stringify(user));


      setInfo(message || "Uspešno Ste prijavljeni.");
      setLoading(false);
      setTimeout(() => {
      navigate('/userpage');
    }, 800);


    }catch(err){ //hvatamo gresku kako nam ne bi pala apl
        setLoading(false);
        if(err.response.status===401){
          setError("Neispravna email adresa ili lozinka. ");
        }else if(err.response.status===422){
          setError("Molimo popunite ispravno sva polja. ");
        }else{
          setError("Došlo je do greške. Pokušajte ponovo.");
        }
  }
    


  
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
        {info && <div className="auth-alert auth-alert-info">{info}</div>}
         {error && <div className="auth-alert auth-alert-error">{error}</div>}
          <button
           type="submit"
           className="btn-primary auth-submit"
           disabled= {loading}
           >
        
          {loading ? "Prijavljivanje..." : "Prijavi se"} 
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