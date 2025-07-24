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

- PHP (v8 recommended but older versions may work)
- Node.js (for building frontend assets)
- [Postmark](https://postmarkapp.com/) account (for signup confirmation emails). You can rework the code to either not use this or use the provider of your choice. Perhaps I can make this easier to customize in a future update.

## Built with

- PHP with [Fat-Free Framework](https://fatfreeframework.com/)
- [Vue.js 2](https://v2.vuejs.org/) + [Vuex](https://v3.vuex.vuejs.org/)
- SQLite database
- [Laravel Mix](https://laravel-mix.com/) for asset compilation

## Developer roadmap

I don't love the dated frontend build step, but I'm also not looking to upgrade to Vite or any of the newer stuff. If I do update the frontend architecture, I will focus on removing the build step altogether and using JavaScript and CSS directly in the browser.