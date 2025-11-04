#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Fonction d'aide
show_help() {
    echo -e "${BLUE}Usage:${NC} ./up.sh [ENV] [OPTIONS]"
    echo ""
    echo "Environments:"
    echo "  dev      - Démarre l'environnement de développement"
    echo "  prod     - Démarre l'environnement de production"
    echo ""
    echo "Options:"
    echo "  --build  - Force la reconstruction des images"
    echo "  --pull   - Pull les dernières images avant de démarrer"
    echo "  -h       - Affiche cette aide"
    echo ""
    echo "Examples:"
    echo "  ./up.sh dev"
    echo "  ./up.sh prod --build"
    echo "  ./up.sh dev --pull"
}

# Vérifier les paramètres
ENV=${1:-dev}
BUILD_FLAG=""
PULL_FLAG=""

# Parser les options
for arg in "$@"; do
    case $arg in
        --build)
            BUILD_FLAG="--build"
            shift
            ;;
        --pull)
            PULL_FLAG="--pull"
            shift
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
    esac
done

# Vérifier que l'environnement est valide
if [[ "$ENV" != "dev" && "$ENV" != "prod" ]]; then
    print_error "Environnement invalide: $ENV"
    echo ""
    show_help
    exit 1
fi

COMPOSE_FILE="docker-compose.${ENV}.yml"

# Vérifier que le fichier compose existe
if [ ! -f "$COMPOSE_FILE" ]; then
    print_error "Fichier $COMPOSE_FILE introuvable!"
    exit 1
fi

print_info "🚀 Démarrage de l'environnement: ${GREEN}$ENV${NC}"
echo ""

# Vérifier si .env existe pour dev
if [[ "$ENV" == "dev" ]] && [ ! -f ".env" ]; then
    print_warning "Fichier .env introuvable. Création avec UID/GID..."
    echo "UID=$(id -u)" > .env
    echo "GID=$(id -g)" >> .env
    echo "DATABASE_URL=postgresql://symfony:symfony@postgres:5432/symfony_dev" >> .env
    echo "XDEBUG_MODE=develop,debug" >> .env
    print_success "Fichier .env créé"
    echo ""
fi

# Créer les dossiers nécessaires si inexistants
if [ ! -d "docker/nginx" ]; then
    print_warning "Dossier docker/nginx introuvable. Création..."
    mkdir -p docker/nginx
fi

# Arrêter les containers existants
print_info "Arrêt des containers existants..."
docker-compose -f "$COMPOSE_FILE" down 2>/dev/null

# Démarrer les services
print_info "Démarrage des services..."
if [ -n "$BUILD_FLAG" ] || [ -n "$PULL_FLAG" ]; then
    docker-compose -f "$COMPOSE_FILE" up -d $BUILD_FLAG $PULL_FLAG
else
    docker-compose -f "$COMPOSE_FILE" up -d
fi

# Vérifier le statut
if [ $? -eq 0 ]; then
    echo ""
    print_success "✅ Environnement $ENV démarré avec succès!"
    echo ""
    
    # Afficher les informations selon l'environnement
    if [[ "$ENV" == "dev" ]]; then
        echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
        echo -e "${BLUE}║${NC}  ${GREEN}Services disponibles (DEV)${NC}                              ${BLUE}║${NC}"
        echo -e "${BLUE}╠════════════════════════════════════════════════════════════╣${NC}"
        echo -e "${BLUE}║${NC}  🌐 Application:     http://localhost:8080                ${BLUE}║${NC}"
        echo -e "${BLUE}║${NC}  📧 MailHog:         http://localhost:8025                ${BLUE}║${NC}"
        echo -e "${BLUE}║${NC}  🗄️  Adminer:         http://localhost:8081                ${BLUE}║${NC}"
        echo -e "${BLUE}║${NC}  🐘 PostgreSQL:      localhost:5432                       ${BLUE}║${NC}"
        echo -e "${BLUE}║${NC}  🔴 Redis:           localhost:6379                       ${BLUE}║${NC}"
        echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
        echo ""
        print_info "Commandes utiles:"
        echo "  • Logs:              docker-compose -f $COMPOSE_FILE logs -f"
        echo "  • Shell PHP:         docker-compose -f $COMPOSE_FILE exec php bash"
        echo "  • Composer install:  docker-compose -f $COMPOSE_FILE exec php composer install"
        echo "  • Clear cache:       docker-compose -f $COMPOSE_FILE exec php php bin/console cache:clear"
        echo "  • Migrations:        docker-compose -f $COMPOSE_FILE exec php php bin/console doctrine:migrations:migrate"
    else
        echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
        echo -e "${BLUE}║${NC}  ${GREEN}Services disponibles (PROD)${NC}                             ${BLUE}║${NC}"
        echo -e "${BLUE}╠════════════════════════════════════════════════════════════╣${NC}"
        echo -e "${BLUE}║${NC}  🌐 Application:     http://localhost                     ${BLUE}║${NC}"
        echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
        echo ""
        print_info "Commandes utiles:"
        echo "  • Logs:        docker-compose -f $COMPOSE_FILE logs -f"
        echo "  • Shell PHP:   docker-compose -f $COMPOSE_FILE exec php sh"
        echo "  • Status:      docker-compose -f $COMPOSE_FILE ps"
    fi
    
    echo ""
    print_info "Statut des containers:"
    docker-compose -f "$COMPOSE_FILE" ps
else
    echo ""
    print_error "❌ Erreur lors du démarrage de l'environnement $ENV"
    echo ""
    print_info "Vérifiez les logs avec:"
    echo "  docker-compose -f $COMPOSE_FILE logs"
    exit 1
fi