import React from 'react'

const PrimaryButton = ({
    type="button",
    loading=false,
    loadingText="Ucitavanje...",
    children,
    className="",
    disabled,
    ...rest
}) => {
  return (
    <button
    type={type}
    className={'btn-primary auth-submit $(className)'}
    disabled={disabled || loading}
    {...rest}
    >
        {loading ? loadingText :children}
    </button>
  );
};

export default PrimaryButton