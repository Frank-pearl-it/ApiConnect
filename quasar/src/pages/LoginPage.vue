<template>
  <q-page style="padding: 0;" class="q-pa-md window-height window-width row justify-center items-center" id="container">
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
            <img src="../assets/logo-thin.svg" alt="Houvast logo" class="logo" width="675"
              style="margin-bottom: -100px;" />
          </div>

          <div v-show="this.userType === null" class="content">
            <h2 class="login-heading">Login met:</h2>
            <div class="row q-col-gutter-lg justify-center">
              <div class="col-12 col-sm-5">
                <q-card @click="handleOfficeClick()" class="rolCard office-card"
                  :class="{ 'loading-card': officeClickLoading }">
                  <q-card-section class="card-content">
                    <div class="centerContent dynamic-avatar-container">
                      <q-spinner v-if="officeClickLoading" color="white" size="4em" :thickness="3" />
                      <q-avatar v-else class="dynamic-avatar" size="80px">
                        <q-icon class="dynamic-icon" name="business" size="48px"></q-icon>
                      </q-avatar>
                    </div>
                    <div class="centerContent text-container">
                      <div class="card-title">
                        {{ officeClickLoading ? 'LADEN...' : 'OFFICE' }}
                      </div>
                      <div class="card-subtitle">
                        {{ officeClickLoading ? '' : 'Microsoft 365 Login' }}
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>

              <div class="col-12 col-sm-5">
                <q-card @click="this.initLogin('2FA')" class="rolCard twofa-card">
                  <q-card-section class="card-content">
                    <div class="centerContent dynamic-avatar-container">
                      <q-avatar class="dynamic-avatar" size="80px">
                        <q-icon class="dynamic-icon" name="lock" size="48px"></q-icon>
                      </q-avatar>
                    </div>
                    <div class="centerContent text-container">
                      <div class="card-title">2FA</div>
                      <div class="card-subtitle">Two-Factor Login</div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </div>

          <div v-if="this.userType === '2FA'" class="content">
            <q-form :greedy='true' @submit.prevent="this.twoFactorLoginProcedure()">
              <!-- Email and Password Step -->
              <div v-show="this.loginStep === 'credentials'">
                <h2><b>Login</b></h2>
                <div class="form-element form-stack">
                  <q-input ref="emailInput" label="E-mail" type="email" name="email" :rules="[
                    val => !!val || 'Dit veld mag niet leeg zijn.',
                    val => /^(?:[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,})$/.test(val) || 'Geen geldig e-mailadres.'
                  ]" v-model="form.email" autofocus></q-input>
                </div>
                <div class="form-element form-stack">
                  <q-input :type="isPwd ? 'password' : 'text'" type="password" name="password" id="password"
                    label="Wachtwoord" :rules="[val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.']"
                    v-model="this.form.password">
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>
                </div>
              </div>

              <!-- Configure 2FA Step -->
              <div v-if="this.loginStep === 'configure'">
                <h2><b>Configureer je 2FA</b></h2>
                <div class="row">
                  <div class="col-9">
                    <div style="padding-right: 12%;" class="form-element form-stack">
                      <q-input label="Code" :rules="[val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.']"
                        v-model="this.form.twoFactorCode" autofocus></q-input>
                    </div>
                  </div>
                  <div class="col">
                    <div id="qrDiv" class="qr-container">
                      <q-inner-loading :showing="loadingQr">
                        <q-spinner color="primary" class="spinner-size" />
                      </q-inner-loading>

                      <!-- 👇 Add this -->
                      <div v-if="!loadingQr && qrSvg" v-html="qrSvg"></div>
                    </div>

                  </div>
                </div>
              </div>

              <!-- Authenticate 2FA Step -->
              <div v-if="this.loginStep === 'authenticate'">
                <h2><b>Authenticeer je 2FA</b></h2>
                <div class="row">
                  <div class="col-9">
                    <div style="padding-right: 12%;" class="form-element form-stack">
                      <q-input label="Code / Recovery code"
                        :rules="[val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.']"
                        v-model="this.form.twoFactorAuthCode" autofocus></q-input>
                    </div>
                  </div>
                  <div class="col">
                    <div class="qr-container">
                      <q-icon size="10rem" name="verified_user" class="full-size-icon" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Reset Password Step -->
              <div v-if="this.loginStep === 'resetPassword'">
                <h2><b>Pas je wachtwoord aan</b></h2>
                <div ref="newPasswordInput" class="form-element form-stack">
                  <q-input standard style="margin-top: 1vh" v-model="this.form.newPassword" label="Nieuw wachtwoord"
                    :type="isPwd ? 'password' : 'text'" id="password" :rules="[
                      val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.'
                    ]" dense autofocus>
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>

                  <q-input standard v-model="this.form.password_confirm" label="Bevestig wachtwoord"
                    :type="isPwd ? 'password' : 'text'" id="password" :rules="[
                      val => val && val.length > 0 || 'Dit veld mag niet leeg zijn.',
                      val => val === form.newPassword || 'Wachtwoorden komen niet overeen.'
                    ]" dense>
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>
                </div>
              </div>
              <!-- Show Recovery Codes Step -->
              <div v-if="loginStep === 'showRecoveryCodes' && recoveryCodes && recoveryCodes.length > 0"
                class="recovery-container">
                <h2><b>Bewaar je herstelcodes veilig</b></h2>
                <p class="text-subtitle1 q-mt-md">
                  Deze codes kunnen worden gebruikt om in te loggen als je je 2FA-apparaat kwijtraakt.
                  Bewaar ze op een veilige plaats. Elke code kan één keer worden gebruikt.
                </p>

                <!-- Virtual scroll list -->
                <q-virtual-scroll class="q-mt-md rounded-borders bg-grey-1" style="max-height: 200px;"
                  :items="recoveryCodes" separator bordered v-slot="{ item: code, index }">
                  <q-item :key="index" dense>
                    <q-item-section>{{ code }}</q-item-section>
                  </q-item>
                </q-virtual-scroll>
              </div>



              <div class="form-element form-submit row justify-between items-start">
                <div class="column">
                  <q-btn v-if="loginStep != 'showRecoveryCodes'" icon-right="arrow_forward" :loading="loginLoading"
                    class="login" type="submit" :label="getButtonLabel()" />

                  <q-btn v-if="loginStep === 'showRecoveryCodes'" color="primary" label="Voltooien"
                    @click="completeLoginAfterRecovery()" />
                </div>
                <!-- TODO make it send a pw reset mail -->
                <q-btn v-if="loginStep === 'credentials'" id="forgotPassword" flat color="primary"
                  label="Wachtwoord vergeten?" class="text-weight-medium no-caps q-pt-xs" @click="forgotPassword" />
                <q-btn v-if="loginStep === 'showRecoveryCodes'" color="primary" icon="file_download"
                  label="Download codes" @click="downloadRecoveryCodes" />
              </div>

              <!-- Show Recovery Codes Step -->
              <!-- Replace your showRecoveryCodes section with this: -->


            </q-form>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>
<script lang="js">
import { defineComponent, nextTick } from 'vue';
import { get, post } from '../../../resources/js/bootstrap.js'
import { popup, initializeCanvas } from "boot/custom-mixin";
import { showLoading, hideLoading } from 'src/utils/loading.js';

export default defineComponent({
  name: 'LoginPage',
  data() {
    return {
      form: {},
      isPwd: true,
      userType: null,
      loginStep: 'credentials', // 'credentials', 'configure', 'showRecoveryCodes', 'authenticate', 'resetPassword'
      twoFactorUser: null, // Store user data for 2FA flow
      loginLoading: false,
      microsoftLoginUrl: 'placeholder',
      microsoftUrlLoading: true,
      officeClickLoading: false,
      loadingQr: false,
      qrSvg: null,
      recoveryCodes: [],

    }
  },
  mounted() {
    initializeCanvas();
    this.$nextTick(() => {
      setTimeout(() => {
        const flashLocal = localStorage.getItem('flashMessage');
        const flashCookie = this.getCookie('flashMessage');
        const raw = flashLocal || flashCookie;

        if (!raw) return;

        let message = raw;
        try {
          const parsed = JSON.parse(raw);
          if (parsed && typeof parsed === 'object' && parsed.message) message = parsed.message;
          else if (typeof parsed === 'string') message = parsed;
        } catch (e) {
          // not JSON, keep raw
        }

        popup({
          status: 400,
          data: { message }
        });

        try { localStorage.removeItem('flashMessage'); } catch (e) { }
        this.deleteCookie('flashMessage');
      }, 300);
    });
  },
  beforeMount() {
    // get('auth/employee/microsoftUrl').then(response => {
    //   this.microsoftLoginUrl = response.data;
    //   this.microsoftUrlLoading = false;
    // }).catch(error => {
    //   popup(error.response);
    //   this.microsoftUrlLoading = false;
    // });
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

    handleCsrfError() {
      localStorage.setItem('flashMessage', 'Token vernieuwd probeer opnieuw in te loggen');
      document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT';
      setTimeout(() => {
        window.location.reload();
      }, 100);
    },

    handleOfficeError(error) {
      if (
        error.response.status === 401 &&
        error.response.data.messages &&
        error.response.data.messages.toString().includes(
          'Uw bedrijf maakt gebruik van Office, gebruik deze login methode in plaats van 2fa.'
        )
      ) {
        this.userType = null;
        this.loginStep = 'credentials';
        return true;
      }
      return false;
    },

    forgotPassword() {
      if (!this.form.email) {
        popup({ data: { message: "Vul een geldig e-mail adres in om wachtwoord te resetten." }, status: 400 });
        return;
      }
      showLoading('Reset mail verzenden...');
      post("sendResetLink", this.form)
        .then((response) => {
          popup(response);
        })
        .catch((error) => {
          this.loginLoading = false;
          const response = error.response;
          if (response?.data?.errors?.message) {
            response.data.message = response.data.errors.message;
          }
          popup(response);
        })
        .finally(() => {
          hideLoading();
        });
    },

    initLogin(type) {
      this.userType = type;

      if (this.userType === 'OFFICE') {
        if (this.microsoftLoginUrl) {
          window.location.href = this.microsoftLoginUrl;
        } else if (!this.microsoftUrlLoading) {
          popup({ data: { message: 'Microsoft login is not available. Please try again.' } });
        }
      }

      if (this.userType === '2FA') {
        nextTick(() => {
          this.$refs.emailInput?.focus();
        });
      }
    },

    handleOfficeClick() {
      if (this.microsoftUrlLoading) {
        this.officeClickLoading = true;
        this.waitForMicrosoftUrl();
      } else {
        this.initLogin('OFFICE');
      }
    },

    waitForMicrosoftUrl() {
      const checkUrl = () => {
        if (!this.microsoftUrlLoading) {
          this.officeClickLoading = false;
          this.initLogin('OFFICE');
        } else {
          setTimeout(checkUrl, 100);
        }
      };
      checkUrl();
    },

    async completeLogin(response) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('profile', JSON.stringify(response.data.user));
      await nextTick()   // wait for DOM/reactivity flush
      this.$router.push({ name: 'dashboard' })
    },

    twoFactorLoginProcedure() {
      this.loginLoading = true;

      // Step 1: Initial login with credentials
      if (this.loginStep === 'credentials') {
        post('login', this.form)
          .then(response => {
            // Check if user has 2FA enabled
            if (response.data.two_factor === false) {
              // User needs to configure 2FA
              this.loginStep = 'configure';
              this.loginLoading = false;
              this.loadingQr = true;
              this.twoFactorUser = response.data.user;

              // Enable 2FA via Fortify
              return post('user/two-factor-authentication');
            } else {
              // User has 2FA configured, move to authentication
              this.twoFactorUser = response.data.user;
              this.loginStep = 'authenticate';
              this.loginLoading = false;
            }
          })
          .then(response => {
            // This handles the 2FA setup response
            if (response && response.status === 200) {
              return get('user/two-factor-qr-code');
            }
          })
          .then(response => {
            if (response) {
              this.qrSvg = response.data.svg; // store it reactively
              this.loadingQr = false;
            }
          })
          .catch(error => {
            this.loginLoading = false;
            this.loadingQr = false;

            if (error.response?.status === 419) {
              this.handleCsrfError();
              return;
            }

            if (this.handleOfficeError(error)) {
              return;
            }

            popup(error.response);
          });
      }

      // Step 2: Confirm 2FA configuration
      else if (this.loginStep === 'configure') {
        post('user/confirmed-two-factor-authentication', { code: this.form.twoFactorCode })
          .then(response => {
            if (response.status === 200) {
              return get('user/two-factor-recovery-codes');
            }
          })
          .then(response => {
            if (response && response.data) {
              this.recoveryCodes = response.data;
              this.loginStep = 'showRecoveryCodes';
              this.loginLoading = false;
            }
          })
          .catch(error => {
            this.loginLoading = false;
            if (error.response?.status === 419) {
              this.handleCsrfError();
              return;
            }
            popup(error.response);
          });

      }

      // Step 3: Authenticate with 2FA code
      else if (this.loginStep === 'authenticate') {
        let challenge = {};

        // Determine if it's a recovery code or regular code
        if (/[a-zA-Z]/.test(this.form.twoFactorAuthCode)) {
          challenge = { recovery_code: this.form.twoFactorAuthCode };
        } else {
          challenge = { code: this.form.twoFactorAuthCode };
        }

        post('two-factor-challenge', challenge)
          .then(response => {
            if (response.status === 200 || response.status === 204) {
              this.loginLoading = false;
              this.completeLogin(response);
            }
          })
          .catch(error => {
            this.loginLoading = false;

            if (error.response?.status === 419) {
              this.handleCsrfError();
              return;
            }

            popup(error.response);
          });
      }

      // Step 4: Reset password (custom route)
      else if (this.loginStep === 'resetPassword') {
        post('auth/client/changePsw', this.form)
          .then(response => {
            this.form.password = this.form.newPassword;
            this.loginStep = 'authenticate';
            this.loginLoading = false;
            popup({
              status: 200,
              data: { message: 'Wachtwoord succesvol gewijzigd. Log nu in met uw nieuwe wachtwoord.' }
            });
          })
          .catch(error => {
            this.loginLoading = false;

            if (error.response?.status === 419) {
              this.handleCsrfError();
              return;
            }

            popup(error.response);
          });
      }
    },
    downloadRecoveryCodes() {
      if (!this.recoveryCodes?.length) {
        popup({ data: { message: "Geen herstelcodes beschikbaar." } });
        return;
      }

      const content = this.recoveryCodes.join('\n');
      const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = '2FA-recovery-codes-Pearl-IT-Klantenportaal.txt';
      link.click();
      URL.revokeObjectURL(link.href);
    },

    completeLoginAfterRecovery() {
      popup({
        status: 200,
        data: { message: '2FA ingesteld. Je bent nu ingelogd.' }
      });

      post('login', {
        email: this.form.email,
        password: this.form.password
      })
        .then(response => {
          console.info('[AFTER RECOVERY] Silent re-login succeeded');
          this.completeLogin(response);
        })
        .catch(err => {
          console.error('[AFTER RECOVERY] Silent re-login failed', err);
          popup(err.response);
        });
    }
    ,

    getButtonLabel() {
      switch (this.loginStep) {
        case 'credentials':
          return 'Inloggen';
        case 'configure':
          return 'Voltooien';
        case 'authenticate':
          return 'Authenticeren';
        case 'resetPassword':
          return 'Wachtwoord wijzigen';
        default:
          return 'Verder';
      }
    },
  }
});
</script>
<style scoped lang="scss">
$theme-signup: var(--q-primary);
$theme-signup-darken: var(--q-primary);
$theme-signup-background: #2C3034;
$theme-login: var(--q-primary);
$theme-login-darken: var(--q-primary);
$theme-login-background: white;
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

.login-heading {
  text-align: center;
  color: $theme-login;
  margin-bottom: 2em;
  font-weight: 300;
  font-size: 2.2em;
  letter-spacing: 0.02em;
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
  height: auto;
  margin-top: 1.5em;
  display: flex;
  flex-direction: column;
  gap: 0.3em;
}

.card-title {
  font-size: 1.8em;
  font-weight: 700;
  letter-spacing: 0.15em;
  color: white;
}

.card-subtitle {
  font-size: 0.95em;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.85);
  letter-spacing: 0.05em;
}

/* Responsive grid */
.rolCard {
  height: 32vh;
  min-height: 280px;
  width: 100%;
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  position: relative;
}

.rolCard::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
  pointer-events: none;
}

.office-card {
  background: linear-gradient(135deg, #d75395 0%, #c13d7e 100%);
}

.twofa-card {
  background: linear-gradient(135deg, #2ab6cb 0%, #1e96aa 100%);
}

.rolCard:hover {
  cursor: pointer;
  transform: translateY(-6px);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
}

.rolCard:active {
  transform: translateY(-2px);
}

.card-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  padding: 2em !important;
}

/* Avatar adjustments */
.dynamic-avatar {
  background-color: rgba(255, 255, 255, 0.25) !important;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.dynamic-avatar-container {
  margin-bottom: 0.5em;
}

/* Adjust icon size */
.dynamic-icon {
  color: white !important;
}

@media only screen and (max-width: 1024px) and (min-width: 600px) {
  .card-title {
    font-size: 1.6em;
  }

  .card-subtitle {
    font-size: 0.9em;
  }

  .rolCard {
    min-height: 260px;
  }
}

@media only screen and (max-width: 600px) and (min-width: 400px) {
  .card-title {
    font-size: 1.5em;
  }

  .card-subtitle {
    font-size: 0.85em;
  }

  .rolCard {
    min-height: 240px;
  }
}

@media only screen and (max-width: 400px) {
  .card-title {
    font-size: 1.4em;
  }

  .card-subtitle {
    font-size: 0.8em;
  }

  .rolCard {
    min-height: 220px;
  }

  .login-heading {
    font-size: 1.8em;
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

  .q-col-gutter-lg {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5em;
  }

  .rolCard {
    width: 90% !important;
    max-width: 400px;
  }

  .col-12 {
    max-width: 100%;
    display: flex;
    justify-content: center;
  }
}

#image {
  height: 35%;
  display: flex;
  justify-content: center;
  align-items: center;
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
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner-size {
  width: 50%;
  height: 50%;
  max-width: 100%;
  max-height: 100%;
  margin: auto;
}

.full-size-icon {
  font-size: 100%;
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  color: var(--q-primary);
}

#forgotPassword {
  border: none !important;
  box-shadow: none !important;
  margin: 0 !important;
}
</style>