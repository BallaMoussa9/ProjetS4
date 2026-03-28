#!/bin/bash

echo "=== Connexion ==="
LOGIN=$(curl -s -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"jean@example.com","password":"password123"}')

TOKEN=$(echo $LOGIN | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "Erreur: Impossible de se connecter"
    echo $LOGIN
    exit 1
fi

echo "Token obtenu: $TOKEN"

echo -e "\n=== 1. Ajout commentaire ==="
curl -s -X POST http://localhost/api/commentaires \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"publication_id":1,"contenu":"Très intéressant comme publication !"}'

echo -e "\n=== 2. Voir commentaires ==="
curl -s -X GET http://localhost/api/commentaires/1 \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n=== 3. Ajouter like ==="
curl -s -X POST http://localhost/api/likes \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"publication_id":1}'

echo -e "\n=== 4. Voir likes ==="
curl -s -X GET http://localhost/api/likes/1 \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n=== 5. Créer publication ==="
curl -s -X POST http://localhost/api/publications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"message":"Nouvelle publication de test"}'

echo -e "\n=== 6. Voir publications ==="
curl -s -X GET http://localhost/api/publications \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n=== 7. Voir utilisateur ==="
curl -s -X GET http://localhost/api/user \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n=== 8. Déconnexion ==="
curl -s -X POST http://localhost/api/logout \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== Test terminé ==="
