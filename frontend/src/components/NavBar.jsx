import React, { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useNavigate } from "react-router-dom";
import api from "../api/api";

//navodimo linkove, omogucavaju kretanje kroz stranice
//da bi mogao da se omoguci logout, svaki put treba u navbaru da proverimo da li je korisnik ulogovan
const NavBar = () => {
  const location=useLocation();//kuka koja omogucava da citamo koji je link trenutno upisan, vraca trenutnu putanju
  //svaki put kada je nova stranica otvorena uzimam lokaciju i svaki put kada se promeni lokacija proveravamo da li je korisnik ulogovan
  //tako sto proverimo da li localstorage ima token
  //koristimo kuku stanja sa kojom prvo predpostavljamo da korisnik nije ulogovan:
  const[isAuth, setIsAuth] = useState(false);
  const navigate = useNavigate();
  //kuka ovaj kod izvrsava svaki put kad se promeni location:
  useEffect(()=> {
    const token=localStorage.getItem("token");
    setIsAuth(!!token);//postavlja da li token postoji ili ne
    console.log("Location changed to:", location.pathname);
    console.log("Is Auth:", isAuth);//da li je ulogovan, na pocetku smo postavili da bude false
    console.log("Token:", token);
  }, [location]);
  
  const handleLogout=async() =>{
    try{
    //laravel: POST/api/logout (zasticena ruta, Sanctum token ide preko interceptora)
    await api.post("/logout");//token saljemo u api.js
    }catch(err){
      console.error("Greska pri logout-u: ", err);
      //cak i ako padne poziv, ocisticemo storage (podatke o useru i tokenu)
    } finally{
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      setIsAuth(false);
      navigate("/login");//sa navigate kukom vodi korisnika na login stranicu
    }
  };
  return (
    //Pocetna da se prikaze svima
    <div className="navbar">
        <Link to="/" className="href">
        Pocetna
        </Link>
        {isAuth ? (
          <button
        type="button"
        className="href href-button"
        onClick={handleLogout}
        >
          Logout
        </button>
        ) : (
          <>
          <Link to="/login" className="href">
            Login
          </Link>
          <Link to="/register" className="href">
            Registracija
            </Link>
          </>
        )}
        </div>
  );
};

export default NavBar