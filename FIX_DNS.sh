#!/bin/bash

echo "🔧 Configuration du DNS Docker pour résoudre les erreurs Alpine..."
echo ""

# Créer le fichier daemon.json
sudo tee /etc/docker/daemon.json > /dev/null << 'EOF'
{
  "dns": ["8.8.8.8", "8.8.4.4", "1.1.1.1"]
}
EOF

if [ $? -eq 0 ]; then
    echo "✅ Fichier /etc/docker/daemon.json créé avec succès"
else
    echo "❌ Erreur lors de la création du fichier"
    exit 1
fi

# Redémarrer Docker
echo ""
echo "🔄 Redémarrage de Docker..."
sudo systemctl restart docker

if [ $? -eq 0 ]; then
    echo "✅ Docker redémarré avec succès"
else
    echo "❌ Erreur lors du redémarrage de Docker"
    exit 1
fi

echo ""
echo "🎉 Configuration terminée!"
echo ""
echo "Vous pouvez maintenant rebuilder votre image:"
echo "  docker build -t tourno-api:v1.0.0 ."
echo "  docker tag tourno-api:v1.0.0 tourno-api:latest"
echo "  docker-compose up -d"
