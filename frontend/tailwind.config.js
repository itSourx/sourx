/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './public/**/*.html',
    './src/**/*.{js,jsx,ts,tsx,vue}',
  ],
  theme: {
    extend: {
      backgroundImage: {
        'img-login': "url('@/assets/bg-login.png')",
      }
    },
    colors: {
      'hr': '#C8C8C8',
      'white': '#ffffff',
      'yellow': '#EEC643',       // Saffron
      'night': '#141414',        // Night
      'anti-flash-white': '#EEF0F2', // Anti-flash white
      'zaffre': '#0D21A1',       // Zaffre
      'oxford-blue': '#011638',  // Oxford Blue
      'gray-light': '#D3D3D3',   // Light Grey
      'gray-medium': '#808080',  // Grey
      'red': '#DC2626',          // Red for errors
      'green': '#16A34A',        // Green for success
      'hover-red': '#B91C1C',     // Red background on hover
      'hover-green': '#15803D'
    },
    
  },
  plugins: [],
}

