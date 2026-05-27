(() => {
  const forms = document.querySelectorAll('[data-auth-form]');
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const getPasswordState = (value) => {
    const checks = {
      length: value.length >= 8,
      uppercase: /[A-Z]/.test(value),
      number: /\d/.test(value),
      lowercase: /[a-z]/.test(value),
      symbol: /[^A-Za-z0-9]/.test(value),
    };

    const score = Object.values(checks).filter(Boolean).length;
    let label = 'Baja';
    let tone = 'danger';

    if (score === 3) {
      label = 'Media';
      tone = 'warning';
    } else if (score === 4) {
      label = 'Alta';
      tone = 'success';
    } else if (score === 5) {
      label = 'Muy alta';
      tone = 'success';
    }

    return {
      checks,
      label,
      tone,
      percent: Math.round((score / 5) * 100),
    };
  };

  const setError = (form, name, message) => {
    const error = form.querySelector(`[data-error-for="${name}"]`);
    const field = form.querySelector(`[name="${name}"]`);

    if (error) {
      error.textContent = message;
    }

    if (field) {
      const wrapper = field.closest('.field, .checkbox-row');

      if (wrapper) {
        wrapper.classList.toggle('is-invalid', message !== '');
      }
    }
  };

  const validateField = (form, field, type) => {
    if (!field) {
      return true;
    }

    const value = field.type === 'checkbox' ? field.checked : field.value.trim();
    let message = '';

    if (field.type === 'checkbox') {
      if (!value) {
        message = 'Debes aceptar los términos y condiciones.';
      }
    } else if (value === '') {
      message = 'Este campo es obligatorio.';
    } else if (field.type === 'email' && !emailPattern.test(value)) {
      message = 'Ingresa un correo electrónico válido.';
    } else if (type === 'register' && field.name === 'password') {
      const state = getPasswordState(field.value);

      if (!(state.checks.length && state.checks.uppercase && state.checks.number)) {
        message = 'La contraseña debe tener mínimo 8 caracteres, 1 mayúscula y 1 número.';
      }
    }

    setError(form, field.name, message);

    return message === '';
  };

  const updatePasswordUi = (form) => {
    const input = form.querySelector('[data-password-input]');

    if (!input) {
      return;
    }

    const state = getPasswordState(input.value);
    const meter = form.querySelector('[data-password-meter]');
    const label = form.querySelector('[data-password-label]');
    const rules = form.querySelectorAll('[data-password-rule]');

    if (meter) {
      meter.style.width = `${state.percent}%`;
      meter.className = `password-meter__value password-meter__value--${state.tone}`;
    }

    if (label) {
      label.textContent = state.label;
    }

    rules.forEach((rule) => {
      const key = rule.getAttribute('data-password-rule');
      rule.classList.toggle('is-met', Boolean(key && state.checks[key]));
    });
  };

  const isFormReady = (form, type) => {
    const fields = [...form.querySelectorAll('[data-required]')];
    const requiredFieldsValid = fields.every((field) => {
      if (field.type === 'checkbox') {
        return field.checked;
      }

      const value = field.value.trim();

      if (value === '') {
        return false;
      }

      if (field.type === 'email') {
        return emailPattern.test(value);
      }

      return true;
    });

    if (!requiredFieldsValid) {
      return false;
    }

    if (type !== 'register') {
      return true;
    }

    const password = form.querySelector('[name="password"]');
    const state = getPasswordState(password ? password.value : '');

    return state.checks.length && state.checks.uppercase && state.checks.number;
  };

  forms.forEach((form) => {
    const type = form.getAttribute('data-auth-form');
    const submitButton = form.querySelector('[data-submit-button]');
    const fields = [...form.querySelectorAll('[data-required]')];

    const refresh = () => {
      updatePasswordUi(form);

      if (submitButton) {
        submitButton.disabled = !isFormReady(form, type);
      }
    };

    fields.forEach((field) => {
      const eventName = field.type === 'checkbox' ? 'change' : 'input';

      field.addEventListener(eventName, () => {
        validateField(form, field, type);
        refresh();
      });

      field.addEventListener('blur', () => {
        validateField(form, field, type);
      });
    });

    form.addEventListener('submit', (event) => {
      let isValid = true;

      fields.forEach((field) => {
        isValid = validateField(form, field, type) && isValid;
      });

      refresh();

      if (!isValid) {
        event.preventDefault();
      }
    });

    refresh();
  });
})();
