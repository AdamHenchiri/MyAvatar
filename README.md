# MyAvatar - README

Bienvenue sur MyAvatar, une application permettant aux utilisateurs de gérer facilement leurs avatars en associant leur adresse e-mail à une photo de profil. Voici quelques informations importantes pour comprendre le fonctionnement de MyAvatar.

## Stockage des Images

Les images de profil sont stockées directement dans la base de données en utilisant l'encodage en base 64. Chaque utilisateur peut associer son adresse e-mail à une photo de profil lors de son inscription. L'image est ensuite encodée en base 64 et enregistrée dans la base de données, offrant ainsi une solution pratique pour gérer les avatars.

## Récupération de l'Encodage de l'Image

Pour récupérer l'encodage de l'image associée à un utilisateur, nous utilisons le cryptage de l'e-mail de l'utilisateur. En passant l'e-mail crypté en tant que paramètre à la route `/my/avatar/{enc}`, l'application renvoie l'encodage en base 64 de l'image correspondante.

## Utilisation de Docker pour Mail Dev

Afin de faciliter la confirmation des e-mails des utilisateurs, nous avons intégré Docker avec Mail Dev. Cette approche nous permet de tester et de vérifier les e-mails de confirmation en toute simplicité. Assurez-vous d'avoir Docker installé pour profiter pleinement de cette fonctionnalité.

## Tailwind CSS pour le Style

Nous avons opté pour l'utilisation de Tailwind CSS pour implémenter les styles de MyAvatar. Tailwind offre une approche utility-first qui facilite la création et la maintenance du CSS. N'hésitez pas à explorer et personnaliser le style en modifiant les classes Tailwind dans le code source.

## Configuration et Installation

1. Clonez ce dépôt sur votre machine locale.
2. Installez les dépendances avec la commande : `composer install && yarn install`
3. Créez un fichier `.env` et configurez les variables d'environnement avec votre base de donnée et le ceci pour mail dev : `MAILER_DSN=smtp://localhost:1025` .
4. Exécutez les migrations pour créer la base de données : `php bin/console doctrine:migrations:migrate`
5. Lancez l'application : `symfony serve`
6. Lancez Mail Dev :  `docker run -p 1080:1080 -p 1025:1025 maildev/maildev`

C'est tout ! Vous êtes prêt à utiliser MyAvatar pour gérer facilement les avatars de vos utilisateurs. En cas de problème ou de question, n'hésitez pas à nous contacter. Merci d'utiliser MyAvatar !
