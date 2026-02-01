import React from 'react'
import { Link } from 'react-router-dom'
//navodimo linkove, omogucavaju kretanje kroz stranice
const NavBar = () => {
  return (
    <div>
        <Link to="/" className="href">Pocetna</Link>
        <Link to="/login" className="href">Login</Link>
        <Link to="/register" className="href">Registracija</Link>
    </div>
  )
}

export default NavBar