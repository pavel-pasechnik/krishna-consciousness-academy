# Krishna Consciousness Academy WordPress Theme

[![Maintainability](https://qlty.sh/gh/pavel-pasechnik/projects/wordpress/maintainability.svg)](https://qlty.sh/gh/pavel-pasechnik/projects/wordpress)
[![Known Vulnerabilities](https://snyk.io/test/github/pavel-pasechnik/wordpress/badge.svg)](https://snyk.io/test/github/pavel-pasechnik/wordpress)
[![Last Commit](https://img.shields.io/github/last-commit/pavel-pasechnik/wordpress?style=flat)](https://github.com/pavel-pasechnik/wordpress/commits/main)

## Description

**Krishna Consciousness Academy WordPress Theme** is a custom WordPress theme (with a set of
internal plugins) for the website of the Krishna Consciousness Academy. The project runs locally in
Docker containers and is automatically configured using WP-CLI.

The theme supports a multisite WordPress configuration and is pre-configured for multilingual use,
including Ukrainian, English, and Russian languages.

## Languages used

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)
![HTML](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/pavel-pasechnik/wordpress.git
   cd wordpress
   ```

2. Create the `.env` file:

   ```bash
   cp .env.example .env
   ```

3. Install dependencies:

   ```bash
   npm i
   ```

4. Build Docker images:

   ```bash
   docker compose build
   ```

5. Start the containers:

   ```bash
   docker compose up -d
   ```

6. Open in browser:
   - Site: [http://localhost:8000](http://localhost:8000)
   - Admin panel: [http://localhost:8000/wp-admin](http://localhost:8000)
   - Login: `admin`, Password: `admin`

7. Stop the containers:

   ```bash
   docker compose down
   ```

## Technologies Used

![WordPress](https://img.shields.io/badge/WordPress-21759b?logo=wordpress&logoColor=white)
![WP-CLI](https://img.shields.io/badge/WP--CLI-000000?logo=wordpress&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white)
![PHP Intelephense](https://img.shields.io/badge/PHP--Intelephense-8892BF?logo=php&logoColor=white)
![Prettier](https://img.shields.io/badge/Prettier-F7B93E?logo=prettier&logoColor=black)
![ESLint](https://img.shields.io/badge/ESLint-4B32C3?logo=eslint&logoColor=white)
![Stylelint](https://img.shields.io/badge/Stylelint-263238?logo=stylelint&logoColor=white)
![HTMLHint](https://img.shields.io/badge/HTMLHint-E34F26?logo=html5&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![Elasticsearch](https://img.shields.io/badge/Elasticsearch-005571?logo=elasticsearch&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-DC382D?logo=redis&logoColor=white)

## License

This project is licensed under the [GPL-2.0](LICENSE).
