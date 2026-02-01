
import React, {useState} from "react";
import {useNavigate} from "react-router-dom";
import api from "../api/api";
import "./LogInPage.css";//koristimo isti css kao za login
import TextInput from "../components/TextInput";
import FileInput from "../components/FileInput";
import PrimaryButton from "../components/PrimaryButton";

const Register = () => {
    const navigate=useNavigate();

    const [ime, setIme]=useState("");
    const [prezime, setPrezime]=useState("");
    const [email, setEmail]=useState("");
    const [password, setPassword]=useState("");
    const [passwordConfirmation, setPasswordConfirmation]=useState("");
    const [slika, setSlika]=useState(null);

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [info, setInfo] = useState("");

    const handleFileChange=(e) => {//fja koja radi sa fajlovima
        const file=e.target.files?.[0];
        setSlika(file || null);
    };

    const handleSubmit= async (e) =>{//kada korisnik popuni formu i klikne submit, poziva se ova fja
    e.preventDefault();
    setError("");
    setInfo("");
    setLoading(true);
//kada saljemo http zahtev i primamo odgovor to su stringovi, a ovde moramo da radimo i sa slikama
//pa zato kreiramo formData - objekat koji omogucava serijalizaciju, omogucava nam da posaljemo objekat fajl
    try{
        const formData=new FormData();//kreiramo prazan
        formData.append("ime", ime);//pa u njega dodajemo polja
        formData.append("prezime", prezime);
        formData.append("email", email);
        formData.append("password", password);
        formData.append("password_confirmation", passwordConfirmation);

        if(slika){//ako postoji slika dodajemo i sliku
            formData.append("slika", slika);
        }

        const res = await api.post("/register", formData);

        const {message} = res.data;//dobijamo odgovor

        setInfo(
            message 
            || "Registracija uspesna. Proverite email i verifikujte nalog."
        );
        setLoading(false);
        //posle kratkog vremena vodi na login
        setTimeout(() => {//kada uradimo registraciju, posle 1200ms nas vodi na login stranicu
            navigate('/login');
        }, 1200);
    } catch(err){//deo za obradu izuzetaka
        console.error(err);
        setLoading(false);
        if(err.response){
        if(err.response.status===422){
            //mozemo izvuci prvu poruku iz validacionih gresaka
            const errors=err.response.data?.errors || {};
            const firstKey=Object.keys(errors)[0];
            const firstMsg=firstKey ? errors[firstKey][0]:null;
            setError(firstMsg || "Validacija nije prosla. Proverite uneta polja");
        }else{
          setError("Došlo je do greške. Pokušajte ponovo.");
        }
        }else{
            setError("Server nije dostupan. Proverite konekciju.");
        }
    }
};

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1 className="auth-title">Registruj se na mojaEUprava portal</h1>
        <p className="auth-subtitle">
          Popunite sledeća polja kako biste se registrovali i započeli korišćenje portala mojaEUprava.
        </p>

        <form  className="auth-form" onSubmit={handleSubmit}>

          <TextInput
          id="ime"
          label="Ime:"
          placeholder="Unesite ime"
          value={ime}
          onChange={(e)=>setIme(e.target.value)}
          required
          />

           <TextInput
          id="prezime"
          label="Prezime:"
          placeholder="Unesite prezime"
          value={prezime}
          onChange={(e)=>setPrezime(e.target.value)}
          required
          />

           <TextInput
          id="email"
          label="Email adresa:"
          type="email"
          placeholder="ime.prezime@example.com"
          value={email}
          onChange={(e)=>setEmail(e.target.value)}
          autoComplete="email"
          required
          />

           <TextInput
          id="password"
          label="Lozinka:"
          type="password"
          placeholder="Unesite lozinku (min 6 karaktera)"
          value={password}
          onChange={(e)=>setPassword(e.target.value)}
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
          onChange={(e)=>setPasswordConfirmation(e.target.value)}
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

        {error && <div className="auth-alert auth-alert-error">{error}</div>}
         {info && <div className="auth-alert auth-alert-info">{info}</div>}
         
          <PrimaryButton
          type="submit"
          loading={loading}
          loadingText="Registracija..."
          >
            Registruj se
          </PrimaryButton>
        </form>
      </div>
    </div>
  );
};

export default Register