#!/bin/bash
# Script de publication simplifié pour sokevinjonas
# Usage: ./publish.sh [version]
# Par défaut: v1.0.0

set -e

VERSION=${1:-v1.0.0}
DOCKERHUB_USERNAME="sokevinjonas"
IMAGE_NAME="tourno-api"
FULL_IMAGE="${DOCKERHUB_USERNAME}/${IMAGE_NAME}"

echo "🚀 Publication Tourno API sur Docker Hub"
echo ""
echo "👤 Username: ${DOCKERHUB_USERNAME}"
echo "📦 Image: ${FULL_IMAGE}"
echo "🏷️  Version: ${VERSION}"
echo ""

# Vérifier la connexion Docker Hub
echo "🔐 Vérification connexion Docker Hub..."
if ! docker info 2>/dev/null | grep -q "Username: ${DOCKERHUB_USERNAME}"; then
    echo "⚠️  Non connecté. Connexion à Docker Hub..."
    docker login -u ${DOCKERHUB_USERNAME}
fi

# Builder l'image localement d'abord
echo ""
echo "🔨 Construction de l'image optimisée..."
docker build -t ${FULL_IMAGE}:${VERSION} -t ${FULL_IMAGE}:latest .

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors du build!"
    exit 1
fi

# Afficher la taille
echo ""
echo "✅ Image construite avec succès!"
docker images ${FULL_IMAGE}:${VERSION}

# Tagger aussi comme tourno-api:v1.0.0 en local
docker tag ${FULL_IMAGE}:${VERSION} tourno-api:${VERSION}
docker tag ${FULL_IMAGE}:latest tourno-api:latest

echo ""
read -p "📤 Publier sur Docker Hub? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "📤 Push ${FULL_IMAGE}:${VERSION}..."
    docker push ${FULL_IMAGE}:${VERSION}

    echo "📤 Push ${FULL_IMAGE}:latest..."
    docker push ${FULL_IMAGE}:latest

    echo ""
    echo "✅ Publication réussie!"
    echo ""
    echo "🌐 Votre image est disponible sur:"
    echo "   https://hub.docker.com/r/${DOCKERHUB_USERNAME}/${IMAGE_NAME}"
    echo ""
    echo "📥 Pour l'utiliser ailleurs:"
    echo "   docker pull ${FULL_IMAGE}:${VERSION}"
    echo "   docker pull ${FULL_IMAGE}:latest"
    echo ""
    echo "💡 Mettez à jour docker-compose.yml:"
    echo "   image: ${FULL_IMAGE}:${VERSION}"
else
    echo "⚠️  Publication annulée."
    echo "🏠 Image disponible localement: tourno-api:${VERSION}"
fi
