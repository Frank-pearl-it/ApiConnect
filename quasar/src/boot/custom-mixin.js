import { Notify } from 'quasar'
import paper from 'paper';
import {startRegistration, startAuthentication} from '@simplewebauthn/browser';
import { post } from '../../../resources/js/bootstrap';

export const popup = (response) => {
  const status = response.status;
  
  if (status >= 200 && status < 300) {
    // Success case: Any status code from 200 to 299
    const successObject = response.data?.messages || response.data?.message || 'Success!';
    
    let successMessages;
    
    if (typeof successObject === 'object' && successObject !== null) {
      // Extract success messages if response.data.messages is an object
      successMessages = Object.values(successObject).flat();
    } else {
      // Handle single string success message
      successMessages = [successObject];
    }

    return Notify.create({
      position: 'top',
      message: `<ul>${successMessages.map(message => `<li>${message}</li>`).join('')}</ul>`,
      icon: 'check_circle',
      color: 'green',
      html: true
    });
  } 
  
  if (status >= 400) {
    // Error case: Any status code from 400 and above
    const errorObject =   response.data?.errors || response.data?.messages || response.data?.message || 'An error occurred';

    let errorMessages;
    
    if (typeof errorObject === 'object' && errorObject !== null) {
      // Extract error messages if response.data.errors is an object
      errorMessages = Object.values(errorObject).flat();
    } else {
      // Handle single string error message
      errorMessages = [errorObject];
    }

    return Notify.create({
      position: 'top',
      message: `<ul>${errorMessages.map(error => `<li>${error}</li>`).join('')}</ul>`,
      icon: 'warning_amber',
      color: 'red',
      html: true
    });
  }
};

export const initializeBiometricLogin = async (request) => {
    try {
        // Step 1: Request biometric login initialization
        const accountResp = await post('auth/client/initBiometricLogin',request);

        // Step 2: Perform authentication
        const authenticatorResponse = await startAuthentication(accountResp.data.publicKeyCredentials);

        // Step 3: Complete biometric login with the server
        const verifyResponse = await post(`auth/client/finishBiometricLogin/${accountResp.data.idUser}`, authenticatorResponse);

        // Step 4: Return token if authentication was successful
        if (verifyResponse.data.token) {
            return verifyResponse.data.token;
        }

        return false;
    } catch (error) {
        popup(error.response);
        return false; // Ensure the function always returns a value
    }
};

export const initializeBiometricRegister = async (idUser) => {
    try {
        // Step 1: Request biometric login initialization
        const accountResp = await post(`auth/client/initBiometricRegister/${idUser}`);

        // Step 2: Perform authentication
        const authenticatorResponse = await startRegistration(accountResp.data.publicKeyCredentials);

        // Step 3: Complete biometric login with the server
        const verifyResponse = await post(`auth/client/finishBiometricVerification/${idUser}`, authenticatorResponse);

        // Step 4: Return token if authentication was successful
        if (verifyResponse.data === 'Registration successful') {
          return true;
        }

        return false;
    } catch (error) {
      console.log(error)
        return false; // Ensure the function always returns a value
    }
};

export const initializeCanvas = () => {
      // ====================== *
      //  Initiate Canvas       *
      // ====================== */

      const canvas = document.getElementById("canvas");
      paper.setup(canvas);  // Initialize Paper.js with the canvas element

      // Ensure Paper.js objects like Group, Path, etc. are accessible
      const { Group, Path, view } = paper;  // Destructure the necessary classes from the paper object

      // Paper JS Variables
      let canvasWidth,
        canvasHeight,
        canvasMiddleX,
        canvasMiddleY;

      let shapeGroup = new Group();
      let positionArray = [];

      const getCanvasBounds = () => {
        // Get current canvas size
        canvasWidth = view.size.width;
        canvasHeight = view.size.height;
        canvasMiddleX = canvasWidth / 2;
        canvasMiddleY = canvasHeight / 2;
        // Set path position
        positionArray = [
          { x: (canvasMiddleX - 50) + (canvasMiddleX / 2), y: 150 },
          { x: 200, y: canvasMiddleY },
          { x: canvasWidth - 130, y: canvasHeight - 75 },
          { x: 0, y: canvasMiddleY + 100 },
          { x: (canvasMiddleX / 2) + 100, y: 100 },
          { x: canvasMiddleX + 80, y: canvasHeight - 50 },
          { x: canvasWidth + 60, y: canvasMiddleY - 50 },
          { x: canvasMiddleX + 100, y: canvasMiddleY + 100 }
        ];
      };

      // ====================== *
      // Create Shapes          *
      // ====================== */
      const initializeShapes = () => {
        // Get Canvas Bounds
        getCanvasBounds();

        const shapePathData = [
          'M231,352l445-156L600,0L452,54L331,3L0,48L231,352',
          'M0,0l64,219L29,343l535,30L478,37l-133,4L0,0z',
          'M0,65l16,138l96,107l270-2L470,0L337,4L0,65z',
          'M333,0L0,94l64,219L29,437l570-151l-196-42L333,0',
          'M331.9,3.6l-331,45l231,304l445-156l-76-196l-148,54L331.9,3.6z',
          'M389,352l92-113l195-43l0,0l0,0L445,48l-80,1L122.7,0L0,275.2L162,297L389,352',
          'M 50 100 L 300 150 L 550 50 L 750 300 L 500 250 L 300 450 L 50 100',
          'M 700 350 L 500 350 L 700 500 L 400 400 L 200 450 L 250 350 L 100 300 L 150 50 L 350 100 L 250 150 L 450 150 L 400 50 L 550 150 L 350 250 L 650 150 L 650 50 L 700 150 L 600 250 L 750 250 L 650 300 L 700 350 '
        ];

        for (let i = 0; i < shapePathData.length; i++) {
          // Create shape
          let headerShape = new Path({
            strokeColor: 'rgba(255, 255, 255, 0.5)',
            strokeWidth: 2,
            parent: shapeGroup,
          });
          // Set path data
          headerShape.pathData = shapePathData[i];
          headerShape.scale(2);
          // Set path position
          headerShape.position = positionArray[i];
        }
      };

      initializeShapes();

      // ====================== *
      // Animation              *
      // ====================== */
      view.onFrame = function paperOnFrame(event) {
        if (event.count % 4 === 0) {
          // Slows down frame rate
          for (let i = 0; i < shapeGroup.children.length; i++) {
            if (i % 2 === 0) {
              shapeGroup.children[i].rotate(-0.1);
            } else {
              shapeGroup.children[i].rotate(0.1);
            }
          }
        }
      };

      view.onResize = function paperOnResize() {
        getCanvasBounds();

        for (let i = 0; i < shapeGroup.children.length; i++) {
          shapeGroup.children[i].position = positionArray[i];
        }

        if (canvasWidth < 700) {
          shapeGroup.children[3].opacity = 0;
          shapeGroup.children[2].opacity = 0;
          shapeGroup.children[5].opacity = 0;
        } else {
          shapeGroup.children[3].opacity = 1;
          shapeGroup.children[2].opacity = 1;
          shapeGroup.children[5].opacity = 1;
        }
      };
    }



