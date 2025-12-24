#!/bin/bash

# Script de publication Docker Hub pour Tourno API
# Usage: ./publish-docker.sh <username> <version>
# Exemple: ./publish-docker.sh myusername 1.0.0

set -e

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifier les arguments
if [ "$#" -lt 2 ]; then
    echo -e "${RED}Usage: $0 <dockerhub-username> <version>${NC}"
    echo "Exemple: $0 myusername 1.0.0"
    exit 1
fi

DOCKERHUB_USERNAME=$1
VERSION=$2
IMAGE_NAME="tourno-api"
FULL_IMAGE_NAME="${DOCKERHUB_USERNAME}/${IMAGE_NAME}"

echo -e "${GREEN}=== Publication Tourno API sur Docker Hub ===${NC}"
echo ""
echo "Username: ${DOCKERHUB_USERNAME}"
echo "Image: ${FULL_IMAGE_NAME}"
echo "Version: ${VERSION}"
echo ""

# Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker n'est pas installé!${NC}"
    exit 1
fi

# Vérifier la connexion Docker Hub
echo -e "${YELLOW}Vérification de la connexion Docker Hub...${NC}"
if ! docker info | grep -q "Username"; then
    echo -e "${YELLOW}Connexion à Docker Hub requise...${NC}"
    docker login
fi

# Construire l'image
echo -e "${GREEN}Construction de l'image Docker...${NC}"
docker build -t ${FULL_IMAGE_NAME}:${VERSION} -t ${FULL_IMAGE_NAME}:latest .

if [ $? -ne 0 ]; then
    echo -e "${RED}Erreur lors de la construction de l'image!${NC}"
    exit 1
fi

# Afficher la taille de l'image
echo -e "${GREEN}Image construite avec succès!${NC}"
docker images ${FULL_IMAGE_NAME}

# Demander confirmation avant push
echo ""
read -p "Voulez-vous publier cette image sur Docker Hub? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${GREEN}Publication de l'image...${NC}"
    
    # Push version spécifique
    echo -e "${YELLOW}Push ${FULL_IMAGE_NAME}:${VERSION}...${NC}"
    docker push ${FULL_IMAGE_NAME}:${VERSION}
    
    # Push latest
    echo -e "${YELLOW}Push ${FULL_IMAGE_NAME}:latest...${NC}"
    docker push ${FULL_IMAGE_NAME}:latest
    
    echo -e "${GREEN}✓ Publication réussie!${NC}"
    echo ""
    echo "Votre image est maintenant disponible sur:"
    echo "  https://hub.docker.com/r/${DOCKERHUB_USERNAME}/${IMAGE_NAME}"
    echo ""
    echo "Pour l'utiliser:"
    echo "  docker pull ${FULL_IMAGE_NAME}:${VERSION}"
    echo "  docker pull ${FULL_IMAGE_NAME}:latest"
else
    echo -e "${YELLOW}Publication annulée.${NC}"
fi
