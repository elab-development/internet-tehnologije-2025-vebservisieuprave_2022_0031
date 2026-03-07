import React from "react";
import "./ZahtevCard.css";

const tipZahtevaCeoTekst = (tip) => {
  if (tip === "prebivaliste") return "Promena prebivališta";
  if (tip === "bracni_status") return "Promena bračnog statusa";
  return tip;
};

const ZahtevCard = ({ zahtev, onClick }) => {
  const datum = new Date(zahtev.datum_kreiranja).toLocaleDateString("sr-RS", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });

  return (
    <div className="zahtev-card" onClick={onClick ? () => onClick(zahtev.id) : null}>
      <h3>
        {zahtev.korisnik.ime} {zahtev.korisnik.prezime}
      </h3>
      <p>Datum kreiranja: {datum}</p>
      <p>Tip zahteva: {tipZahtevaCeoTekst(zahtev.tip_zahteva)}</p>
    </div>
  );
};

export default ZahtevCard;