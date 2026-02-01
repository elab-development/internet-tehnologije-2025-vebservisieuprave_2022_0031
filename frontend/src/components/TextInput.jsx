import React, { useState } from "react";
import { FaEyeSlash } from "react-icons/fa";
import { FaEye } from "react-icons/fa";

const TextInput = ({
    id,
    label,
    type="text",
    value,
    onChange,
    placeholder,
    required=false,
    autoComplete,
    hint,
    showPasswordToggle=false, //ako je tekstualno polje, ne treba da imamo mogucnost otkrivanja i sakrivanja teksta
    ...rest //ako jos nesto zelimo da dodamo
}) => {
//zahtev - javascript funkcionalnost -> da prekrivamo i sakrivamo (toglujemo) lozinku
//pretpostavljamo da korisnik na pocetku ne zeli da mu lozinka bude otkrivena (stavljamo da je false)
//to treba da prikazemo samo ako je input tipa password
//ako je tipa password sakriveno, ako je tipa text otkriveno
const [showPassword, setShowPassword]=useState(false);
const isPassword=type === "password"; //ako jeste pass ovo ce biti true
//racunamo koji je tip:
const inputType=isPassword && showPasswordToggle ? (showPassword ? "text":"password") : type;
//stavljamo div auth-input-wrapper da bi dugme sakrij(prikazi) i input polje za password pripadali istom divu - istoj celini  
  return (
    <div className="auth-field">
            {label && <label htmlFor={id}>{label}</label>}
            <div className="auth-input-wrapper">
            <input
              id={id}
              type={inputType}
              className=""
              placeholder={placeholder}
              value={value}
              onChange={onChange}
              required={required}
              autoComplete={autoComplete}
              {...rest}
            />
            {isPassword && showPasswordToggle && (
                <button
                type="button"
                className="toggle-password-btn"
                onClick={()=>setShowPassword((prev) => !prev)}
                >
                    {showPassword ?  <FaEye /> : <FaEyeSlash />}
                </button>
            )}
            </div>
            {hint && <small className="auth-hint">{hint}</small>}
    </div>
  )
}

export default TextInput