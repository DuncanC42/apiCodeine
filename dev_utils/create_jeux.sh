#!/bin/bash

# URL de l'API
API_URL="http://localhost:8000/api/games"

# Jeu 1
curl -X POST $API_URL \
-H "Content-Type: application/json" \
-d '{
    "nom": "Jeu 1",
    "etape": 1,
    "nb_niveau": 10,
    "description": "Description du Jeu 1",
    "regles": "Règles du Jeu 1",
    "message_fin": "Message de fin du Jeu 1",
    "photo": "photo1.jpg",
    "temps_max": 60
}'

# Jeu 2
curl -X POST $API_URL \
-H "Content-Type: application/json" \
-d '{
    "nom": "Jeu 2",
    "etape": 2,
    "nb_niveau": 15,
    "description": "Description du Jeu 2",
    "regles": "Règles du Jeu 2",
    "message_fin": "Message de fin du Jeu 2",
    "photo": "photo2.jpg",
    "temps_max": 120
}'

# Jeu 3
curl -X POST $API_URL \
-H "Content-Type: application/json" \
-d '{
    "nom": "Jeu 3",
    "etape": 3,
    "nb_niveau": 20,
    "description": "Description du Jeu 3",
    "regles": "Règles du Jeu 3",
    "message_fin": "Message de fin du Jeu 3",
    "photo": "photo3.jpg",
    "temps_max": 180
}'

# Jeu 4
curl -X POST $API_URL \
-H "Content-Type: application/json" \
-d '{
    "nom": "Jeu 4",
    "etape": 4,
    "nb_niveau": 25,
    "description": "Description du Jeu 4",
    "regles": "Règles du Jeu 4",
    "message_fin": "Message de fin du Jeu 4",
    "photo": "photo4.jpg",
    "temps_max": 240
}'

# Jeu 5
curl -X POST $API_URL \
-H "Content-Type: application/json" \
-d '{
    "nom": "Jeu 5",
    "etape": 5,
    "nb_niveau": 30,
    "description": "Description du Jeu 5",
    "regles": "Règles du Jeu 5",
    "message_fin": "Message de fin du Jeu 5",
    "photo": "photo5.jpg",
    "temps_max": 300
}'