import React from "react";
import "./HomePage.css";
import FeatureCard from "../components/FeatureCard";
import { FaCalendarDays } from "react-icons/fa6";
import { FaUserEdit } from "react-icons/fa";
import { FaHouseChimney } from "react-icons/fa6";
import { GiDiamondRing } from "react-icons/gi";
import { IoIosLock } from "react-icons/io";
import { FaUser } from "react-icons/fa";

const HomePage = ({ user }) => {
  const FEATURES= [
  {
    id: 1,
    title: "Kreiranje naloga",
    description: "Napravite nalog brzo i jednostavno, i odmah pristupite svim funkcijama.",
    icon: <FaUserEdit />
  },
  {
    id: 2,
    title: "Zakazivanje termina",
    description: "Rezervišite termin za lične karte i pasoše online, izbegnite čekanje u redovima.",
    icon: <FaCalendarDays />
  },
  {
    id: 3,
    title: "Promena prebivališta",
    description: "Podnesite zahtev za promenu prebivališta elektronski, brzo i jednostavno.",
    icon: <FaHouseChimney />
  },
  {
    id: 4,
    title: "Promena bračnog statusa",
    description: "Jednostavno ažurirajte svoj bračni status putem e-Uprave, bez papirologije.",
    icon: <GiDiamondRing />
  },
  {
    id: 5,
    title: "Sigurnost podataka",
    description: "Vaši podaci su potpuno sigurni i zaštićeni modernim tehnologijama.",
    icon: <IoIosLock />
  }
];
const formatTipKorisnika = (tip) => {
    if (!tip) return "Nepoznato";
    return tip === "domaci" ? "Domaći" : "Strani";
  };

  return (
    <div className="homepage-container">

      {/* --- HERO SEKCIJA --- */}
      <div className="hero">
        {/* Leva strana */}
        <div className="hero-left">
          <h1>Dobrodošli na mojaEUprava portal!</h1>
          <p className="moto">
            Brzo i sigurno obavite svoje administrativne zahteve online, bez čekanja u redovima.
          </p>
          <p className="about">
            Portal mojaEUprava omogućava građanima da podnose zahteve, zakazuju termine i prate status dokumenata elektronski, štedeći vreme i olakšavajući proces administracije.
          </p>
         
        </div>

        
             <div className="hero-right">
          <div className="user-card">
            <div className="avatar"><FaUser /></div>
            
            {user ? (
              <>
                <h3>{user.ime} {user.prezime}</h3>
                <p><strong>Email:</strong> {user.email}</p>
                <p><strong>Tip:</strong> {formatTipKorisnika(user.tip_korisnika)}</p>
              </>
            ) : (
              <p>Niste prijavljeni.</p>
            )}

          </div>
        </div>
      </div>
   
  


      {/* --- SEKCIJA: ČEMU SLUŽI E-UPRAVA --- */}
      <div className="features-container">
         <h2>Čemu služi mojaEUprava?</h2> 
        <div className="features-grid">
            {FEATURES.map(feature => (
            <FeatureCard
              key={feature.id}
              title={feature.title}
              description={feature.description}
              icon={feature.icon}
            />
          ))}
        

        </div>
      </div>

    </div>
  );
};

export default HomePage;