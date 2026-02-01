import React from 'react'

const FileInput = ({
    id,
    label,
    accept,
    onChange,
    hint,
    required=false,
    ...rest //ako jos nesto zelimo da dodamo
}) => {
  return (
    <div className="auth-field">
            {label && <label htmlFor={id}>{label}</label>}
            <input
              id={id}
              type="file"
              className=""
              accept={accept}
              onChange={onChange}
              required={required}
              {...rest}
            />
            {hint && <small className="auth-hint">{hint}</small>}
    </div>
  )
}

export default FileInput