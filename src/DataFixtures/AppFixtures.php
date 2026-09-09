<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('LudoShop');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setBirthDate(new \DateTimeImmutable('1990-01-01'));
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $admin->setEmailVerifiedAt(new \DateTimeImmutable());
        $manager->persist($admin);

        $root = new User();
        $root->setEmail('root@root.com');
        $root->setFirstName('Root');
        $root->setLastName('SuperUser');
        $root->setRoles(['ROLE_ADMIN']);
        $root->setBirthDate(new \DateTimeImmutable('1985-06-15'));
        $root->setPassword($this->passwordHasher->hashPassword($root, 'Root123!'));
        $root->setEmailVerifiedAt(new \DateTimeImmutable());
        $manager->persist($root);

        $client = new User();
        $client->setEmail('client@example.com');
        $client->setFirstName('Claire');
        $client->setLastName('Client');
        $client->setBirthDate(new \DateTimeImmutable('1995-03-20'));
        $client->setPassword($this->passwordHasher->hashPassword($client, 'Client123!'));
        $client->setEmailVerifiedAt(new \DateTimeImmutable());
        $manager->persist($client);

        $minor = new User();
        $minor->setEmail('minor@example.com');
        $minor->setFirstName('Mina');
        $minor->setLastName('Mineur');
        $minor->setBirthDate(new \DateTimeImmutable('2014-07-10'));
        $minor->setPassword($this->passwordHasher->hashPassword($minor, 'Minor123!'));
        $minor->setEmailVerifiedAt(new \DateTimeImmutable());
        $manager->persist($minor);

        $categories = [
            ['name' => 'Stratégie', 'slug' => 'strategie'],
            ['name' => 'Famille', 'slug' => 'famille'],
            ['name' => 'Ambiance', 'slug' => 'ambiance'],
            ['name' => 'Coopératif', 'slug' => 'cooperatif'],
        ];

        /** @var array<string, Category> $categoryMap */
        $categoryMap = [];
        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat['name']);
            $category->setSlug($cat['slug']);
            $manager->persist($category);
            $categoryMap[$cat['slug']] = $category;
        }

        $products = [
            ['name' => 'Catan', 'reference' => 'CAT-001', 'publisher' => 'Kosmos', 'price' => 42.90, 'stock' => 12, 'description' => 'Le jeu de colonisation qui a conquis le monde : construisez, échangez et conquérez l\'île de Catan.', 'image' => 'https://placehold.co/600x400/E8D44D/333?text=Catan', 'specs' => ['playtime' => 90, 'setup' => 5, 'minAge' => 10, 'minPlayers' => 3, 'maxPlayers' => 4], 'categories' => ['strategie', 'famille']],
            ['name' => 'Dixit', 'reference' => 'DIX-001', 'publisher' => 'Libellud', 'price' => 29.90, 'stock' => 20, 'description' => 'Un jeu d\'association d\'idées aux illustrations oniriques. Faites deviner vos cartes sans être trop clair.', 'image' => 'https://placehold.co/600x400/9B59B6/FFF?text=Dixit', 'specs' => ['playtime' => 30, 'setup' => 2, 'minAge' => 8, 'minPlayers' => 3, 'maxPlayers' => 6], 'categories' => ['famille', 'ambiance']],
            ['name' => '7 Wonders', 'reference' => '7WO-001', 'publisher' => 'Repos Production', 'price' => 39.90, 'stock' => 8, 'description' => 'Devenez le leader d\'une des sept grandes cités du monde antique et faites rayonner votre civilisation.', 'image' => 'https://placehold.co/600x400/3498DB/FFF?text=7+Wonders', 'specs' => ['playtime' => 30, 'setup' => 10, 'minAge' => 10, 'minPlayers' => 3, 'maxPlayers' => 7], 'categories' => ['strategie']],
            ['name' => 'Code Names', 'reference' => 'COD-001', 'publisher' => 'Iello', 'price' => 19.90, 'stock' => 25, 'description' => 'Deux équipes, un maître-espion, et des mots à faire deviner. Attention aux agents adverses !', 'image' => 'https://placehold.co/600x400/2C3E50/FFF?text=Code+Names', 'specs' => ['playtime' => 15, 'setup' => 2, 'minAge' => 14, 'minPlayers' => 2, 'maxPlayers' => 8], 'categories' => ['ambiance']],
            ['name' => 'Kingdomino', 'reference' => 'KIN-001', 'publisher' => 'Blue Orange', 'price' => 14.90, 'stock' => 30, 'description' => 'Assemblez des dominos pour construire le plus beau royaume. Un jeu rapide et malin pour toute la famille.', 'image' => 'https://placehold.co/600x400/27AE60/FFF?text=Kingdomino', 'specs' => ['playtime' => 15, 'setup' => 2, 'minAge' => 8, 'minPlayers' => 2, 'maxPlayers' => 4], 'categories' => ['famille', 'strategie']],
            ['name' => 'Azul', 'reference' => 'AZU-001', 'publisher' => 'Plan B Games', 'price' => 34.90, 'stock' => 15, 'description' => 'Décorez le palais de l\'Alhambra en posant les plus beaux azulejos. Un jeu de stratégie élégant.', 'image' => 'https://placehold.co/600x400/1ABC9C/FFF?text=Azul', 'specs' => ['playtime' => 45, 'setup' => 5, 'minAge' => 8, 'minPlayers' => 2, 'maxPlayers' => 4], 'categories' => ['strategie']],
            ['name' => 'Pandemic', 'reference' => 'PAN-001', 'publisher' => 'Z-Man Games', 'price' => 44.90, 'stock' => 6, 'description' => 'Coopérez pour sauver l\'humanité de quatre épidémies meurtrières avant qu\'il ne soit trop tard.', 'image' => 'https://placehold.co/600x400/E74C3C/FFF?text=Pandemic', 'specs' => ['playtime' => 45, 'setup' => 10, 'minAge' => 8, 'minPlayers' => 2, 'maxPlayers' => 4], 'categories' => ['cooperatif', 'strategie']],
            ['name' => 'Carcassonne', 'reference' => 'CAR-001', 'publisher' => 'Hans im Glück', 'price' => 32.90, 'stock' => 18, 'description' => 'Posez des tuiles et placez vos partisans pour construire villes, routes et abbayes.', 'image' => 'https://placehold.co/600x400/8E44AD/FFF?text=Carcassonne', 'specs' => ['playtime' => 35, 'setup' => 5, 'minAge' => 7, 'minPlayers' => 2, 'maxPlayers' => 5], 'categories' => ['famille']],
            ['name' => 'Uno', 'reference' => 'UNO-001', 'publisher' => 'Mattel', 'price' => 9.90, 'stock' => 40, 'description' => 'Le célèbre jeu de cartes qui retourne toutes les situations. Dernière carte... UNO !', 'image' => 'https://placehold.co/600x400/F39C12/FFF?text=Uno', 'specs' => ['playtime' => 15, 'setup' => 1, 'minAge' => 7, 'minPlayers' => 2, 'maxPlayers' => 10], 'categories' => ['famille', 'ambiance']],
            ['name' => 'Wingspan', 'reference' => 'WIN-001', 'publisher' => 'Stonemaier Games', 'price' => 54.90, 'stock' => 5, 'description' => 'Attirez les plus beaux oiseaux dans vos réserves naturelles et développez votre avifaune.', 'image' => 'https://placehold.co/600x400/16A085/FFF?text=Wingspan', 'specs' => ['playtime' => 40, 'setup' => 10, 'minAge' => 10, 'minPlayers' => 1, 'maxPlayers' => 5], 'categories' => ['strategie', 'cooperatif']],
            ['name' => 'Jungle Speed', 'reference' => 'JUN-001', 'publisher' => 'Asmodee', 'price' => 24.90, 'stock' => 22, 'description' => 'Le totem ! Le réflexe avant tout ! Un jeu d\'observation endiablé et nerveux.', 'image' => 'https://placehold.co/600x400/D35400/FFF?text=Jungle+Speed', 'specs' => ['playtime' => 10, 'setup' => 1, 'minAge' => 7, 'minPlayers' => 2, 'maxPlayers' => 8], 'categories' => ['famille', 'ambiance']],
            ['name' => 'Exploding Kittens', 'reference' => 'EXP-001', 'publisher' => 'Exploding Kittens', 'price' => 22.90, 'stock' => 14, 'description' => 'Un jeu de cartes stratégique et explosif. Ne piochez pas un chaton explosif !', 'image' => 'https://placehold.co/600x400/E91E63/FFF?text=Exploding+Kittens', 'specs' => ['playtime' => 15, 'setup' => 1, 'minAge' => 7, 'minPlayers' => 2, 'maxPlayers' => 5], 'categories' => ['ambiance']],
            ['name' => 'Limite Limite', 'reference' => 'LIM-001', 'publisher' => 'Aux Jeux, Citoyens !', 'price' => 24.90, 'stock' => 10, 'description' => 'Le jeu d\'ambiance sans limites ni tabous : répondez aux défis les plus déjantés et assumez vos choix.', 'image' => 'https://placehold.co/600x400/C0392B/FFF?text=Limite+Limite', 'mature' => true, 'specs' => ['playtime' => 30, 'setup' => 1, 'minAge' => 18, 'minPlayers' => 3, 'maxPlayers' => 10], 'categories' => ['ambiance']],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setReference($data['reference']);
            $product->setPublisher($data['publisher']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);
            $product->setDescription($data['description']);
            $product->setIsMature($data['mature'] ?? false);
            $product->setImage($data['image']);
            $specs = array_replace(
                ['playtime' => null, 'setup' => null, 'minAge' => null, 'maxAge' => null, 'minPlayers' => null, 'maxPlayers' => null],
                $data['specs'],
            );
            $product->setPlaytimeMinutes($specs['playtime']);
            $product->setSetupMinutes($specs['setup']);
            $product->setMinAge($specs['minAge']);
            $product->setMaxAge($specs['maxAge']);
            $product->setMinPlayers($specs['minPlayers']);
            $product->setMaxPlayers($specs['maxPlayers']);

            foreach ($data['categories'] as $slug) {
                $product->addCategory($categoryMap[$slug]);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}
