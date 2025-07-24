# Minimal Character Sheet

A digital character sheet for D&D 5e. Create, edit, and manage your characters online with features for abilities, skills, spells, equipment, and more. Share character sheets with others or keep them private.

## Setup

1. Install PHP dependencies:
   ```bash
   composer install
   ```

2. Install Node.js dependencies and build assets:
   ```bash
   npm install
   npm run prod
   ```

3. Create a `.env` file with your Postmark API key:
   ```
   POSTMARK_SECRET=your_postmark_api_key_here
   ```

4. **Secure the data folder** - ensure your web server blocks access to the `/data` directory as it contains the SQLite database.

## Requirements

- PHP
- Node.js (for building frontend assets)
- Web server (Apache/Nginx)
- Postmark account (for signup confirmation emails)

## Built With

- PHP with Fat-Free Framework
- Vue.js 2 + Vuex
- SQLite database
- Laravel Mix for asset compilation