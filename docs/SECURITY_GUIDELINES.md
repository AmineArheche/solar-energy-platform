# Directives de Sécurité — Plateforme Solaire

## Bonnes Pratiques Implémentées :
1. **Protection contre les Injections SQL** : Requêtes 100% préparées via PDO.
2. **Protection CSRF** : Validation stricte des jetons sur toute requête de modification d'état (POST).
3. **Protection XSS** : Échappement obligatoire avec `e()` (`htmlspecialchars` UTF-8).
4. **Gestion des Sessions** : Cookies `HttpOnly`, `SameSite=Lax` et `Secure`.
5. **Mots de Passe** : Hachage robuste avec `password_hash()` (Argon2id / Bcrypt).
