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

   ```dotenv
   POSTMARK_SECRET="your_postmark_api_key_here"
   POSTMAR_FROM="fromemail@example.com" # new user emails will come from this address
   ENV="DEVELOPMENT" # emails are sent to the Postmark test address instead of the user's email address
   ALLOW_SIGNUPS=1 # optional, if you want to let people register accounts
   ADMIN_ONLY=1 # optional, restrict logging in to only admin users
   ```

4. **Secure the data folder** - ensure your web server blocks access to the `/data` directory as it contains the SQLite database.

## Requirements

- PHP (v8 recommended but older versions may work)
- Node.js (for building frontend assets)
- [Postmark](https://postmarkapp.com/) account (for signup confirmation emails). If you want to opt out of using Postmark, you can omit the environmental variables listed above. It just means you will need to manually confirm new signups. You can rework the code to either not use this or use the provider of your choice. Perhaps I can make this easier to customize in a future update.

## Built with

- PHP with [Fat-Free Framework](https://fatfreeframework.com/)
- SQLite database
- [Vue.js 3](https://vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Vite](https://vite.dev/) for asset compilation

## Developer roadmap

- Export character sheets in plain text (Markdown)
- A nicer way for groups to share their character sheets with each other
