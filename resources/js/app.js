// resources/js/app.js atau resources/js/bootstrap.js
import { v4 as uuidv4 } from 'uuid';

// Daftarkan ke window agar bisa diakses global di browser
window.uuidv4 = uuidv4;