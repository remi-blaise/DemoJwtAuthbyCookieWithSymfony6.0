# Demo: Comment construire une authentification par JWT, stateless, avec Symfony (6.0)

## Documentation Technique

### Auth par JWT

Les pages privées (ici /profile) sont protégées par un firewall.

Pour faire une authentification stateless, le firewall a l'option `stateless: true`
(ce qui désactive l'utilisation de la session par Symfony).

L'utilisateur est donc récupéré grâce aux données du JWT par `App\Security\UserProvider`
sans aucune requête à la base de donnée !

L'authenticateur `App\Security\JwtAuthenticator` s'occupe d'authentifier l'utilisateur,
c'est à dire de vérifier la validité du token (signature + date de péremption).
En cas d'échec de la validité, il redirige vers `app_login`.

Le `App\Security\AuthenticationEntryPoint` s'occupe de rediriger également vers `app_login`
les utilisateurs non authentifiés.

La page `app_login` s'occupe de vérifier la validité des identifiants (username + mot de passe)
à la connexion, de générer le JWT et de l'inscrire dans le cookie.
La page `app_register` fait également le travail d'autoconnecter l'utilisateur.

Une classe utilitaire `App\Security\JwtManager` possède les 3 méthodes
`createJwt()`, `verifyJwt()`, `getJwtPlayload()`
afin de rassembler la logique liée à la gestion des JWT en un même endroit.
