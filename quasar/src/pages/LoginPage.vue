<template>
  <q-page style="padding: 0" class="q-pa-md window-height window-width row justify-center items-center" id="container">
    <div id="back">
      <canvas id="canvas" class="canvas-back"></canvas>
      <div class="backRight"></div>
      <div class="backLeft"></div>
    </div>

    <div id="slideBox">
      <div class="topLayer">
        <div class="left hide-on-mobile"></div> <!-- Hide left on mobile -->
        <div class="right">
          <div id="image">
            <img src="../assets/logo.svg" alt="Houvast logo" class="logo" width="275" />
          </div>

          <div v-show="this.userType === null" class="content">
            <div class="row q-col-gutter-md justify-center">
              <div class="col-12 col-sm-6">
                <q-card @click="handleMedewerkerClick()" style="background-color: #d75395;" class="rolCard"
                  :class="{ 'loading-card': medewerkersClickLoading }">
                  <q-card-section>
                    <div class="centerContent dynamic-avatar-container">
                      <!-- Show loading spinner when clicked but URL not ready -->
                      <q-spinner v-if="medewerkersClickLoading" color="white" size="3em" class="dynamic-avatar" />
                      <q-avatar v-else class="dynamic-avatar" color="white">
                        <q-icon class="dynamic-icon" name="groups"></q-icon>
                      </q-avatar>
                    </div>
                    <div class="centerContent text-container">
                      <span>
                        <b>{{ medewerkersClickLoading ? 'LADEN...' : 'MEDEWERKER' }}</b>
                      </span>
                    </div>
                  </q-card-section>
                </q-card>
              </div>

              <div class="col-12 col-sm-6">
                <q-card @click="this.initLogin('CLIENT')" style="background-color: #2ab6cb;" class="rolCard">
                  <q-card-section>
                    <div class="centerContent dynamic-avatar-container">
                      <q-avatar class="dynamic-avatar" color="white">
                        <q-icon class="dynamic-icon" name="emoji_people"></q-icon>
                      </q-avatar>
                    </div>
                    <div class="centerContent text-container">
                      <span><b>CLIËNT</b></span>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </div>

          <div v-if="this.userType === 'CLIENT'" class="content">
            <q-form :greedy='true' @submit.prevent="this.clientLoginProcedure()">
              <div v-show="this.client === null">
                <h2><b>Wat is je e-mail?</b></h2>
                <div class="form-element form-stack">
                  <q-input ref="emailInput" label="E-mail"
                    :rules="[val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.']" v-model="this.form.email"
                    autofocus></q-input>
                </div>
              </div>

              <div v-if="this.client !== null && !this.passwordNeedsReset">
                <h2><b>Wat is je wachtwoord?</b></h2>
                <div ref="passwordInput" class="form-element form-stack">
                  <q-input autofocus :type="isPwd ? 'password' : 'text'" id="password" label="Wachtwoord"
                    :rules="[val => !client || (val && val.length > 0) || 'Dit veld mag niet leeg zijn.']"
                    type="password" v-model="this.form.password">
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>

                </div>
              </div>

              <div v-if="passwordNeedsReset">
                <h2><b>Pas je wachtwoord aan</b></h2>
                <div ref="newPasswordInput" class="form-element form-stack">
                  <q-input standard style="margin-top: 1vh" v-model="this.form.newPassword" label="New password"
                    :type="isPwd ? 'password' : 'text'" id="password" :rules="passwordNeedsReset ? [
                      val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.'
                    ] : []" dense autofocus>
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>

                  <q-input standard v-model="this.form.password_confirm" label="Password confirm"
                    :type="isPwd ? 'password' : 'text'" id="password" :rules="passwordNeedsReset ? [
                      val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.',
                      val => val === form.newPassword || 'Passwords must match.'
                    ] : []" dense>
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>
                </div>
              </div>


              <div class="form-element form-submit">
                <q-btn icon-right="arrow_forward" :loading="this.loginLoading" class="login" type="submit"
                  label="Verder"></q-btn>

                <q-btn style="margin-left: 1vw;" v-if="this.client !== null && this.client.biometricsRegistered === 1"
                  icon-right="fingerprint" class="login" @click="this.initBiometricLogin"
                  :loading="this.biometricLoginLoading" type="button" label="Biometrische inlog"></q-btn>

                <q-btn icon-right="fingerprint" :loading="this.loginLoading" class="login" type="button"
                  v-if="this.passwordNeedsReset" @click="this.registerBiometrics"
                  label="Registreer biometrisch inloggen"></q-btn>
              </div>

            </q-form>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, nextTick } from 'vue';
import { get, post } from '../../../resources/js/bootstrap.js'
import { popup, initializeCanvas, initializeBiometricLogin, initializeBiometricRegister } from "boot/custom-mixin";

export default defineComponent({
  name: 'LoginPage',
  data() {
    return {
      form: {},
      isPwd: true,
      userType: null,
      statusLogin: 'name',
      client: null,
      loginLoading: false,
      biometricLoginLoading: false,
      microsoftLoginUrl: null,
      passwordNeedsReset: false,
      microsoftUrlLoading: true, // Add loading state for Microsoft URL
      medewerkersClickLoading: false,
    }
  },
  mounted() {
    initializeCanvas()
    this.$nextTick(() => {
      setTimeout(() => {
        const flashLocal = localStorage.getItem('flashMessage');
        const flashCookie = this.getCookie('flashMessage');
        const raw = flashLocal || flashCookie;

        if (!raw) return;

        // If you sometimes store objects: try JSON.parse, otherwise use string as-is
        let message = raw;
        try {
          const parsed = JSON.parse(raw);
          // support { message: '...' } or plain string in JSON
          if (parsed && typeof parsed === 'object' && parsed.message) message = parsed.message;
          else if (typeof parsed === 'string') message = parsed;
        } catch (e) {
          // not JSON, keep raw
        }

        popup({
          status: 400,
          data: { message }
        });

        // cleanup
        try { localStorage.removeItem('flashMessage'); } catch (e) { }
        // If you set a cookie with a custom domain (e.g. '.example.com'), pass that domain here:
        this.deleteCookie('flashMessage'); // or this.deleteCookie('flashMessage', '.example.com')
      }, 300); // keep your small delay
    });
  },
  beforeMount() {
    get('auth/employee/microsoftUrl').then(response => {
      this.microsoftLoginUrl = response.data;
      this.microsoftUrlLoading = false; // URL is now loaded
    }).catch(error => {
      popup(error.response);
      this.microsoftUrlLoading = false; // Still set to false even on error
    })
  },
  methods: {
    getCookie(name) {
      const matches = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
      return matches ? decodeURIComponent(matches[1]) : null;
    },
    deleteCookie(name, domain = null) {
      let cookieStr = `${name}=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;`;
      if (domain) cookieStr += ` Domain=${domain};`;
      document.cookie = cookieStr;
    },
    async registerBiometrics() {
      try {
        const success = await initializeBiometricRegister(this.client.id);

        if (success) {
          const message = {
            status: 200, // Ensure popup() gets a valid status
            data: {
              messages: { success: 'Biometrische inlog succesvol geregistreerd!' } // Correctly formatted message
            }
          };
          popup(message);
        } else {
          const errorMessage = {
            status: 400,
            data: {
              message: 'Biometrische registratie mislukt. Probeer het opnieuw.'
            }
          };
          popup(errorMessage);
        }
      } catch (error) {
        const errorMessage = {
          status: 500,
          data: {
            message: 'Er is een interne fout opgetreden bij de biometrische registratie.'
          }
        };
        popup(errorMessage);
      }
    },
    initLogin(type) {
      this.userType = type;

      if (this.userType === 'MEDEWERKER') {
        if (this.microsoftLoginUrl) {
          // URL is ready, proceed with login
          window.location.href = this.microsoftLoginUrl;
        } else if (!this.microsoftUrlLoading) {
          // URL failed to load, show error or retry
          popup({ data: { message: 'Microsoft login is not available. Please try again.' } });
        }
        // If microsoftUrlLoading is true, the loading state is already handled in the template
      }

      if (this.userType === 'CLIENT') {
        nextTick(() => {
          this.$refs.emailInput?.focus();
        });
      }
    },

    handleMedewerkerClick() {
      if (this.microsoftUrlLoading) {
        // Show loading state
        this.medewerkersClickLoading = true;
        // Wait for URL to be loaded
        this.waitForMicrosoftUrl();
      } else {
        // URL is ready or failed, proceed normally
        this.initLogin('MEDEWERKER');
      }
    },

    waitForMicrosoftUrl() {
      const checkUrl = () => {
        if (!this.microsoftUrlLoading) {
          // URL loading finished (success or failure)
          this.medewerkersClickLoading = false;
          this.initLogin('MEDEWERKER');
        } else {
          // Still loading, check again in 100ms
          setTimeout(checkUrl, 100);
        }
      };
      checkUrl();
    },
    clientLoginProcedure() {
      this.loginLoading = true;

      if (this.client === null) {
        post('auth/client/initLogin', this.form)
          .then(response => {
            this.client = response.data;
            this.loginLoading = false;
            nextTick(() => {
              this.$refs.passwordInput?.focus();
            });
          })
          .catch(error => {
            this.loginLoading = false;

            if (error.response) {
              // Handle CSRF mismatch
              if (error.response.status === 419) {
                localStorage.setItem('flashMessage', 'Token vernieuwd probeer opnieuw in te loggen');

                document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT';

                setTimeout(() => {
                  window.location.reload();
                }, 100); // just enough to flush
                return;
              }



              // Handle normal auth error (non-clients must use Microsoft)
              if (
                error.response.status === 401 &&
                error.response.data.messages &&
                error.response.data.messages.toString().includes(
                  'Non clienten mogen alleen inloggen met Microsoft.'
                )
              ) {
                this.userType = null;
              }

              // Default popup
              popup(error.response);
            } else {
              // Fallback if no response (network issue, etc.)
              popup({ data: { message: 'Er ging iets mis. Probeer het later opnieuw.' } });
            }
          });
      } else if (this.client && !this.passwordNeedsReset) {
        post('auth/client/finishLogin', this.form).then(response => {
          if (response.status === 201) {
            this.passwordNeedsReset = true;
            this.loginLoading = false;
            nextTick(() => {
              this.$refs.newPasswordInput?.focus();
            });
          } else {
            this.loginLoading = false;
            localStorage.setItem('token', response.data.token)
            localStorage.setItem('profile', JSON.stringify(this.client))
            this.$router.push({ name: 'dashboard' })
          }
        }).catch(error => {
          this.loginLoading = false;
          popup(error.response);
          if (error.status === 401 && error.data.messages.includes('Non clienten mogen alleen inloggen met Microsoft.')) {
            this.userType = null;
          }
        })

      } else if (this.client && this.passwordNeedsReset) {
        post('auth/client/changePsw', this.form).then(response => {
          this.form.password = this.form.newPassword;
          post('auth/client/finishLogin', this.form).then(response => {
            this.loginLoading = false;
            localStorage.setItem('token', response.data.token)
            localStorage.setItem('profile', JSON.stringify(this.client))
            this.$router.push({ name: 'dashboard' })
          })

        }).catch(error => {
          this.loginLoading = false;
          if (error.response) {
            popup(error.response);
          } else {
            console.error('Network error or no response from server:', error);
            popup({ message: 'Network error or server unreachable.' });
          }
        });

      }
    },
    async initBiometricLogin() {
      this.biometricLoginLoading = true
      let token = await initializeBiometricLogin(this.form);
      if (token) {
        localStorage.setItem('token', token)
        localStorage.setItem('profile', JSON.stringify(this.client))
        this.$router.push({ name: 'dashboard' })
      }
      this.biometricLoginLoading = false;
    }
  }
});
</script>
<style scoped lang="scss">
$theme-signup: var(--q-primary);
$theme-signup-darken: var(--q-primary);
$theme-signup-background: #2C3034;
$theme-login: var(--q-primary);
$theme-login-darken: var(--q-primary);
$theme-login-background: #f9f9f9;
$theme-dark: #212121;
$theme-light: #e3e3e3;
$font-default: 'Roboto', sans-serif;

$success: #5cb85c;
$error: #d9534f;

.centerContent {
  display: flex;
  justify-content: center;
  align-items: center;
}

.q-input {
  font-size: 1.0em;
}

.q-field__control,
.q-field__native {
  font-size: 1.0em;
}

.q-field__label {
  font-size: 1.0em;
}

.text-container {
  height: 8vh;
  font-size: 22px;
  letter-spacing: 0.2rem;
  color: white;
}

/* Responsive grid */
.rolCard {
  height: 28vh;
  padding: 2vh;
  width: 100%;
  margin: 1vw;
}

.rolCard:hover {
  cursor: pointer;
}

/* Avatar adjustments */
.dynamic-avatar {
  width: 5vw;
  height: 5vw;
}

/* Adjust icon size */
.dynamic-icon {
  font-size: 3vw;
}

@media only screen and (max-width: 1024px) and (min-width: 600px) {
  .dynamic-avatar {
    width: 10vw !important;
    /* Increase size slightly for better scaling */
    height: 10vw !important;
  }

  .dynamic-icon {
    font-size: 6vw !important;
  }
}

@media only screen and (max-width: 600px) and (min-width: 400px) {
  .dynamic-avatar {
    width: 15vw;
    height: 15vw;
    max-width: 100px;
    max-height: 100px;
  }

  .dynamic-icon {
    font-size: 8vw;
    max-width: 70px;
    max-height: 70px;
  }
}

@media only screen and (max-width: 400px) {
  .dynamic-avatar {
    width: 25vw !important;
    height: 25vw !important;
    margin-bottom: 1vh;
  }

  .dynamic-icon {
    font-size: 18vw !important;
  }
}

/* Mobile adjustments */
@media only screen and (max-width: 1023px) {

  .signup-info,
  .login-info {
    display: none;
  }

  #slideBox {
    width: 100% !important;
    margin-left: 0 !important;
  }

  .q-col-gutter-md {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .rolCard {
    width: 100% !important;
  }

  /* Make both divs same size */
  .col-12 {
    max-width: 100%;
  }

  .dynamic-avatar {
    width: 15vw;
    height: 15vw;
    max-width: 100px;
    max-height: 100px;
  }

  .dynamic-icon {
    font-size: 8vw;
    max-width: 70px;
    max-height: 70px;
  }
}

#image {
  height: 35%;
  display: flex;
  justify-content: center;
  /* Centers horizontally */
  align-items: center;
  /* Centers vertically */
}

body {
  margin: 0;
  height: 100%;
  overflow: hidden;
  width: 100% !important;
  box-sizing: border-box;
  font-family: $font-default;
}

.backRight {
  position: absolute;
  right: 0;
  width: 50%;
  height: 100%;
  background: $theme-signup;
}

.backLeft {
  position: absolute;
  left: 0;
  width: 50%;
  height: 100%;
  background: $theme-login;
}

#back {
  width: 100%;
  height: 100%;
  position: absolute;
  z-index: -999;
}

.canvas-back {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10;
}

#slideBox {
  width: 50%;
  max-height: 100%;
  height: 100%;
  overflow: hidden;
  margin-left: 50%;
  position: absolute;
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
}

.topLayer {
  width: 200%;
  height: 100%;
  position: relative;
  left: 0;
  left: -100%;
}

label {
  font-size: 0.8em;
}

input {
  background-color: transparent;
  border: 0;
  outline: 0;
  padding: 8px 1px;
  margin-top: 0.1em;
}

.left {
  width: 50%;
  height: 100%;
  overflow: scroll;
  background: $theme-signup-background;
  left: 0;
  position: absolute;

  label {
    color: $theme-light;
  }

  input {
    border-bottom: 1px solid $theme-light;
    color: $theme-light;

    &:focus,
    &:active {
      border-color: $theme-signup;
      color: $theme-signup;
    }

    &:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 30px $theme-signup-background inset;
      -webkit-text-fill-color: $theme-light;
    }
  }

  a {
    color: $theme-signup;
  }
}

.right {
  width: 50%;
  height: 100%;
  overflow: hidden;
  background: $theme-login-background;
  right: 0;
  position: absolute;

  label {
    color: $theme-dark;
  }

  input {
    border-bottom: 1px solid $theme-dark;

    &:focus,
    &:active {
      border-color: $theme-login;
    }

    &:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 30px $theme-login-background inset;
      -webkit-text-fill-color: $theme-dark;
    }
  }
}

.content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 65%;
  width: 80%;
  margin: 0 auto;
  position: relative;
}

.content h2 {
  font-weight: 300;
  font-size: 2.6em;
  margin: 0.2em 0 0.1em;
}

.left .content h2 {
  color: $theme-signup;
}

.right .content h2 {
  color: $theme-login;
}

.form-element {
  margin: 1.6em 0;

  &.form-submit {
    margin: 1.6em 0 0;
  }
}

.form-stack {
  display: flex;
  flex-direction: column;
}

.checkbox {
  -webkit-appearance: none;
  outline: none;
  background-color: $theme-light;
  border: 1px solid $theme-light;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05);
  padding: 12px;
  border-radius: 4px;
  display: inline-block;
  position: relative;
}

.checkbox:focus,
.checkbox:checked:focus,
.checkbox:active,
.checkbox:checked:active {
  border-color: $theme-signup;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px 1px 3px rgba(0, 0, 0, 0.1);
}

.checkbox:checked {
  outline: none;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05), inset 15px 10px -12px rgba(255, 255, 255, 0.1);
}

.checkbox:checked:after {
  outline: none;
  content: '\2713';
  color: $theme-signup;
  font-size: 1.4em;
  font-weight: 900;
  position: absolute;
  top: -4px;
  left: 4px;
}

.form-checkbox {
  display: flex;
  align-items: center;

  label {
    margin: 0 6px 0;
    font-size: 0.72em;
  }
}

button {
  padding: 0.8em 1.2em;
  margin: 0 10px 0 0;
  width: auto;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 1em;
  color: #fff;
  line-height: 1em;
  letter-spacing: 0.6px;
  border-radius: 3px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.1);
  border: 0;
  outline: 0;
  transition: all 0.25s;

  &.signup {
    background: $theme-signup;
  }

  &.login {
    background: $theme-login;
  }

  &.off {
    background: none;
    box-shadow: none;
    margin: 0;

    &.signup {
      color: $theme-signup;
    }

    &.login {
      color: $theme-login;
    }
  }
}

button:focus,
button:active,
button:hover {
  box-shadow: 0 4px 7px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.1);

  &.signup {
    background: $theme-signup-darken;
  }

  &.login {
    background: $theme-login-darken;
  }

  &.off {
    box-shadow: none;

    &.signup {
      color: $theme-signup;
      background: $theme-dark;
    }

    &.login {
      color: $theme-login-darken;
      background: $theme-light;
    }
  }
}

#qrDiv {
  width: 110%;
}

.qr-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.spinner-size {
  width: 50%;
  /* Adjust as needed */
  height: 50%;
  /* Adjust as needed */
  max-width: 100%;
  /* Ensure it doesn't overflow */
  max-height: 100%;
  /* Ensure it doesn't overflow */
  margin: auto;
  /* Center the spinner within the div */
}

.qr-container {
  position: relative;
  width: 100%;
  /* Ensure it takes up the full column space */
  height: 100%;
  /* Adjust as necessary */
  display: flex;
  justify-content: center;
  align-items: center;
}

.full-size-icon {
  font-size: 100%;
  /* Scale to parent container */
  width: 100%;
  height: 100%;
  max-width: 100%;
  /* Prevent overflow */
  max-height: 100%;
}
</style>