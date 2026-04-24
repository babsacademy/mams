const PRODUCTS = [
  {
    id: 1,
    name: "Rich Girl Power",
    type: "human_hair",
    price: 55000,
    badge: null,
    description: "La classe est le choix parfait pour celles qui recherchent une beauté discrète.",
    lengths: ['14"', '16"', '18"', '20"'],
    colors: ["Noir naturel", "Brun chocolat", "Blonde miel"],
    textures: ["Lisse", "Ondulée"],
    laceTypes: ["Lace Front", "Full Lace"],
    rating: 4.8,
    reviews: 24,
    inStock: true,
    isNew: true,
    colorSwatches: ["#141414", "#4b2e21", "#c89b62"],
    gallery: ["Rich Girl Power 01", "Rich Girl Power 02", "Rich Girl Power 03", "Rich Girl Power 04"]
  },
  {
    id: 2,
    name: "Soft Glam Wig",
    type: "blend_hair",
    price: 38000,
    badge: null,
    description: "Perruque de la gloire — incroyablement top. Qualité premium.",
    lengths: ['14"', '16"', '18"'],
    colors: ["Noir naturel", "Brun foncé"],
    textures: ["Bouclée", "Ondulée"],
    laceTypes: ["Headband", "Lace Front"],
    rating: 4.9,
    reviews: 31,
    inStock: true,
    isNew: true,
    colorSwatches: ["#131313", "#2f2118"],
    gallery: ["Soft Glam Wig 01", "Soft Glam Wig 02", "Soft Glam Wig 03", "Soft Glam Wig 04"]
  },
  {
    id: 3,
    name: "Luxury Vibes Hair",
    type: "human_hair",
    price: 72000,
    badge: "Premium",
    description: "Boucles luxueuses — un look diamant pour chaque occasion.",
    lengths: ['16"', '18"', '20"'],
    colors: ["Noir naturel", "Brun chocolat", "Caramel"],
    textures: ["Bouclée"],
    laceTypes: ["Full Lace"],
    rating: 5.0,
    reviews: 18,
    inStock: true,
    isNew: false,
    colorSwatches: ["#121212", "#4b2e21", "#b0753d"],
    gallery: ["Luxury Vibes Hair 01", "Luxury Vibes Hair 02", "Luxury Vibes Hair 03", "Luxury Vibes Hair 04"]
  },
  {
    id: 4,
    name: "Pretty Girl Wig",
    type: "blend_hair",
    price: 42000,
    badge: "Stock limité",
    description: "Effet naturel · Volume + brillance incroyables · Facile à porter.",
    lengths: ['14"', '16"'],
    colors: ["Noir naturel", "Blonde"],
    textures: ["Lisse"],
    laceTypes: ["Lace Front", "Headband"],
    rating: 4.7,
    reviews: 42,
    inStock: true,
    isNew: false,
    colorSwatches: ["#161616", "#d8bb8b"],
    gallery: ["Pretty Girl Wig 01", "Pretty Girl Wig 02", "Pretty Girl Wig 03", "Pretty Girl Wig 04"]
  },
  {
    id: 5,
    name: "Baddie Caramel Wig",
    type: "blend_hair",
    price: 45000,
    badge: null,
    description: "Couleur caramel · Effet naturel + volume · Style luxe toutes occasions.",
    lengths: ['14"', '16"', '18"', '20"'],
    colors: ["Caramel", "Brun caramel", "Blonde caramel"],
    textures: ["Lisse", "Ondulée"],
    laceTypes: ["Lace Front", "Full Lace", "Headband"],
    rating: 4.9,
    reviews: 27,
    inStock: true,
    isNew: true,
    colorSwatches: ["#aa6c39", "#76472b", "#d2a16b"],
    gallery: ["Baddie Caramel Wig 01", "Baddie Caramel Wig 02", "Baddie Caramel Wig 03", "Baddie Caramel Wig 04"]
  },
  {
    id: 6,
    name: "Royal Silk Unit",
    type: "human_hair",
    price: 69000,
    badge: "Nouveau",
    description: "Finition soyeuse avec raie naturelle et volume éditorial.",
    lengths: ['14"', '16"', '18"', '20"'],
    colors: ["Noir naturel", "Espresso"],
    textures: ["Lisse", "Ondulée"],
    laceTypes: ["Lace Front"],
    rating: 4.8,
    reviews: 16,
    inStock: true,
    isNew: true,
    colorSwatches: ["#111111", "#2b1d14"],
    gallery: ["Royal Silk Unit 01", "Royal Silk Unit 02", "Royal Silk Unit 03", "Royal Silk Unit 04"]
  },
  {
    id: 7,
    name: "Velvet Crown Curls",
    type: "human_hair",
    price: 76000,
    badge: "Premium",
    description: "Boucles rebondies haute densité pour un effet glamour immédiat.",
    lengths: ['16"', '18"', '20"'],
    colors: ["Noir naturel", "Brun chocolat"],
    textures: ["Bouclée"],
    laceTypes: ["Full Lace"],
    rating: 4.9,
    reviews: 22,
    inStock: true,
    isNew: false,
    colorSwatches: ["#0e0e0e", "#4f3426"],
    gallery: ["Velvet Crown Curls 01", "Velvet Crown Curls 02", "Velvet Crown Curls 03", "Velvet Crown Curls 04"]
  },
  {
    id: 8,
    name: "Urban Muse Headband",
    type: "blend_hair",
    price: 35000,
    badge: "Stock limité",
    description: "Solution rapide et stylée pour une pose sans effort.",
    lengths: ['14"', '16"', '18"'],
    colors: ["Noir naturel", "Brun foncé", "Caramel"],
    textures: ["Lisse", "Ondulée"],
    laceTypes: ["Headband"],
    rating: 4.6,
    reviews: 39,
    inStock: true,
    isNew: false,
    colorSwatches: ["#141414", "#302019", "#a97142"],
    gallery: ["Urban Muse Headband 01", "Urban Muse Headband 02", "Urban Muse Headband 03", "Urban Muse Headband 04"]
  }
];

const REVIEWS = [
  {
    id: 1,
    productId: 3,
    firstName: "Awa",
    city: "Dakar",
    date: "2026-03-12",
    rating: 5,
    avatar: "A",
    text: "Je reçois des compliments partout. La texture est magnifique et la pose est ultra naturelle.",
    hasPhoto: true
  },
  {
    id: 2,
    productId: 2,
    firstName: "Fatou",
    city: "Thiès",
    date: "2026-03-06",
    rating: 5,
    avatar: "F",
    text: "Livraison rapide et qualité au rendez-vous. Même après plusieurs coiffages, elle reste belle.",
    hasPhoto: true
  },
  {
    id: 3,
    productId: 4,
    firstName: "Khady",
    city: "Dakar",
    date: "2026-02-28",
    rating: 4,
    avatar: "K",
    text: "Volume parfait pour le quotidien. Très facile à entretenir et confortable toute la journée.",
    hasPhoto: false
  },
  {
    id: 4,
    productId: 1,
    firstName: "Ndeye",
    city: "Saint-Louis",
    date: "2026-02-20",
    rating: 5,
    avatar: "N",
    text: "Texture premium comme en salon. C’est ma meilleure commande cette année.",
    hasPhoto: true
  },
  {
    id: 5,
    productId: 5,
    firstName: "Marième",
    city: "Dakar",
    date: "2026-02-09",
    rating: 5,
    avatar: "M",
    text: "La couleur caramel est sublime sur peau noire. Rendu chic et moderne.",
    hasPhoto: true
  },
  {
    id: 6,
    productId: 7,
    firstName: "Adja",
    city: "Rufisque",
    date: "2026-01-30",
    rating: 4,
    avatar: "A",
    text: "Très belles boucles, belles finitions. Je recommande à 100% pour les événements.",
    hasPhoto: false
  }
];

const LOOKBOOK_ITEMS = Array.from({ length: 12 }).map((_, index) => ({
  id: index + 1,
  title: `Look ${index + 1}`,
  heightClass: ["h-56", "h-72", "h-80", "h-64"][index % 4]
}));

const BEFORE_AFTER_CASES = [
  { id: 1, title: "Natural Glow", before: "Avant", after: "Après" },
  { id: 2, title: "Soft Glam", before: "Avant", after: "Après" },
  { id: 3, title: "Luxury Waves", before: "Avant", after: "Après" }
];

window.MAMS_DATA = {
  PRODUCTS,
  REVIEWS,
  LOOKBOOK_ITEMS,
  BEFORE_AFTER_CASES
};
