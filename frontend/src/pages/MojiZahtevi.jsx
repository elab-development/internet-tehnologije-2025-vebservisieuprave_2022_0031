import React, { useEffect, useState } from 'react';
import api from '../api/api';
import './MojiZahtevi.css';



const MojiZahtevi = () => {
    const [zahtevi, setZahtevi] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");
 

  
  const loadZahtevi= async ()=>{
    setLoading(true);
    setError("");
    try {
        const res=await api.get("/zahtev/moje");
        console.log(res.data.data);
        setZahtevi(res.data.data||[]);
    } catch (error) {
        console.error(error);
        setError("Ne mogu da ucitam zahteve.Pokusaj ponovo");

    }finally{
        setLoading(false);
    }

  };
  

  useEffect(() => {
    loadZahtevi();
  }, []);

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
  {zahtevi.length === 0 ? (
    <p className="no-zahtevi">Nemaš nijedan zahtev. Dodaj novi zahtev!</p>
  ) : (
    zahtevi.map((z) => {
      // Mapiranje tipa zahteva
      let tipZahtevaLabel = "";
      if (z.tip_zahteva === "bracni_status") {
        tipZahtevaLabel = "Promena bračnog statusa";
      } else if (z.tip_zahteva === "prebivaliste") {
        tipZahtevaLabel = "Promena prebivališta";
      } else {
        tipZahtevaLabel = z.tip_zahteva;
      }

      // Boja statusa
      let statusClass = "";
      if (z.status === "odobren") {
        statusClass = "status-odobren";
      } else if (z.status === "odbijen") {
        statusClass = "status-odbijen";
      } else {
        statusClass = "status-kreiran";
      }

      return (
        <div key={z.id} className="zahtev-item">
          <h3>{tipZahtevaLabel}</h3>
          {z.tip_promene && <p>Tip promene: {z.tip_promene}</p>}
          <p className={statusClass}>Status: {z.status}</p>
          <p>Datum kreiranja: {new Date(z.datum_kreiranja).toLocaleDateString()}</p>
        </div>
      );
    })
  )}
</div>

        
      </div>
    </div>


  );
}


export default MojiZahtevi

