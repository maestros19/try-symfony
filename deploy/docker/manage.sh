#!/bin/bash

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

print_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }

show_help() {
    cat << EOF
${CYAN}╔════════════════════════════════════════════════════════════╗
║         Symfony Docker Management Script                  ║
╚════════════════════════════════════════════════════════════╝${NC}

${BLUE}Usage:${NC} ./manage.sh [ENV] [COMMAND] [OPTIONS]

${YELLOW}Environments:${NC}
  dev, prod

${YELLOW}Commands:${NC}
  ${GREEN}up${NC}              - Démarre l'environnement
  ${GREEN}down${NC}            - Arrête l'environnement
  ${GREEN}restart${NC}         - Redémarre l'environnement
  ${GREEN}logs${NC}            - Affiche les logs (-f pour follow)
  ${GREEN}ps${NC}              - Liste les containers
  ${GREEN}exec${NC}            - Exécute une commande dans le container PHP
  ${GREEN}bash${NC}            - Ouvre un shell dans le container PHP
  ${GREEN}composer${NC}        - Exécute Composer
  ${GREEN}console${NC}         - Exécute Symfony console
  ${GREEN}cache${NC}           - Clear le cache Symfony
  ${GREEN}migrate${NC}         - Exécute les migrations
  ${GREEN}db-reset${NC}        - Reset la base de données (⚠️  perte de données)
  ${GREEN}status${NC}          - Affiche le statut complet
  ${GREEN}clean${NC}           - Nettoie les containers et volumes

${YELLOW}Examples:${NC}
  ./manage.sh dev up
  ./manage.sh dev logs -f
  ./manage.sh dev exec "php bin/console debug:router"
  ./manage.sh dev composer install
  ./manage.sh dev console make:entity
  ./manage.sh prod status

EOF
}

# Vérifier les paramètres
if [ $# -lt 2 ]; then
    show_help
    exit 1
fi

ENV=$1
COMMAND=$2
shift 2

COMPOSE_FILE="docker-compose.${ENV}.yml"

# Vérifier l'environnement
if [[ "$ENV" != "dev" && "$ENV" != "prod" ]]; then
    print_error "Environnement invalide: $ENV"
    exit 1
fi

# Vérifier que le fichier compose existe
if [ ! -f "$COMPOSE_FILE" ]; then
    print_error "Fichier $COMPOSE_FILE introuvable!"
    exit 1
fi

# Exécuter la commande
case $COMMAND in
    up)
        print_info "Démarrage de l'environnement $ENV..."
        ./up.sh "$ENV" "$@"
        ;;
    
    down)
        print_info "Arrêt de l'environnement $ENV..."
        ./down.sh "$ENV" "$@"
        ;;
    
    restart)
        print_info "Redémarrage de l'environnement $ENV..."
        ./down.sh "$ENV"
        echo ""
        ./up.sh "$ENV"
        ;;
    
    logs)
        docker-compose -f "$COMPOSE_FILE" logs "$@"
        ;;
    
    ps)
        docker-compose -f "$COMPOSE_FILE" ps
        ;;
    
    exec)
        if [ -z "$1" ]; then
            print_error "Commande manquante. Usage: ./manage.sh $ENV exec \"votre commande\""
            exit 1
        fi
        docker-compose -f "$COMPOSE_FILE" exec php $@
        ;;
    
    bash|shell|sh)
        print_info "Ouverture du shell dans le container PHP..."
        if [[ "$ENV" == "prod" ]]; then
            docker-compose -f "$COMPOSE_FILE" exec php sh
        else
            docker-compose -f "$COMPOSE_FILE" exec php bash
        fi
        ;;
    
    composer)
        print_info "Exécution de Composer..."
        docker-compose -f "$COMPOSE_FILE" exec php composer "$@"
        ;;
    
    console)
        print_info "Exécution de Symfony Console..."
        docker-compose -f "$COMPOSE_FILE" exec php php bin/console "$@"
        ;;
    
    cache|cc)
        print_info "Clear du cache Symfony..."
        docker-compose -f "$COMPOSE_FILE" exec php php bin/console cache:clear
        print_success "Cache vidé"
        ;;
    
    migrate)
        print_info "Exécution des migrations..."
        docker-compose -f "$COMPOSE_FILE" exec php php bin/console doctrine:migrations:migrate --no-interaction
        ;;
    
    db-reset)
        print_warning "⚠️  ATTENTION: Cette action va supprimer toutes les données!"
        read -p "Êtes-vous sûr? (oui/non): " confirm
        if [[ "$confirm" == "oui" ]]; then
            print_info "Reset de la base de données..."
            docker-compose -f "$COMPOSE_FILE" exec php php bin/console doctrine:database:drop --force --if-exists
            docker-compose -f "$COMPOSE_FILE" exec php php bin/console doctrine:database:create
            docker-compose -f "$COMPOSE_FILE" exec php php bin/console doctrine:migrations:migrate --no-interaction
            print_success "Base de données réinitialisée"
        else
            print_info "Opération annulée"
        fi
        ;;
    
    status)
        echo ""
        print_info "═══════════════════════════════════════════════════════════"
        print_info "  STATUS - Environnement: ${GREEN}$ENV${NC}"
        print_info "═══════════════════════════════════════════════════════════"
        echo ""
        
        echo -e "${CYAN}Containers:${NC}"
        docker-compose -f "$COMPOSE_FILE" ps
        
        echo ""
        echo -e "${CYAN}Volumes:${NC}"
        docker volume ls | grep -E "(${ENV}|symfony)" || echo "Aucun volume trouvé"
        
        echo ""
        echo -e "${CYAN}Images:${NC}"
        docker images | grep -E "(symfony|postgres|nginx|redis|mailhog)" | head -10
        
        echo ""
        echo -e "${CYAN}Réseau:${NC}"
        docker network ls | grep "${ENV}" || echo "Aucun réseau trouvé"
        
        echo ""
        ;;
    
    clean)
        print_warning "⚠️  Nettoyage des ressources Docker..."
        read -p "Supprimer les volumes? (oui/non): " volumes
        
        if [[ "$volumes" == "oui" ]]; then
            docker-compose -f "$COMPOSE_FILE" down -v --remove-orphans
            print_success "Containers et volumes supprimés"
        else
            docker-compose -f "$COMPOSE_FILE" down --remove-orphans
            print_success "Containers supprimés"
        fi
        
        print_info "Pour nettoyer complètement Docker:"
        echo "  docker system prune -a --volumes"
        ;;
    
    *)
        print_error "Commande inconnue: $COMMAND"
        echo ""
        show_help
        exit 1
        ;;
esac