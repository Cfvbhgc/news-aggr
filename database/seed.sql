-- Seed data for NewsAggr

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Technology', 'technology'),
(2, 'Science', 'science'),
(3, 'Business', 'business'),
(4, 'World News', 'world-news'),
(5, 'Entertainment', 'entertainment');

-- Feeds (real RSS URLs)
INSERT INTO `feeds` (`id`, `name`, `url`, `category_id`, `is_active`) VALUES
(1, 'TechCrunch', 'https://techcrunch.com/feed/', 1, 1),
(2, 'Ars Technica', 'https://feeds.arstechnica.com/arstechnica/index', 1, 1),
(3, 'Habr (English)', 'https://habr.com/en/rss/articles/', 1, 1),
(4, 'BBC News - World', 'https://feeds.bbci.co.uk/news/world/rss.xml', 4, 1),
(5, 'Reuters - World', 'https://www.reutersagency.com/feed/', 4, 1),
(6, 'NASA Breaking News', 'https://www.nasa.gov/rss/dyn/breaking_news.rss', 2, 1),
(7, 'Science Daily', 'https://www.sciencedaily.com/rss/all.xml', 2, 1),
(8, 'BBC News - Business', 'https://feeds.bbci.co.uk/news/business/rss.xml', 3, 1),
(9, 'The Verge', 'https://www.theverge.com/rss/index.xml', 5, 1),
(10, 'Hacker News - Best', 'https://hnrss.org/best', 1, 1);

-- Sample articles
INSERT INTO `articles` (`feed_id`, `title`, `content`, `url`, `author`, `published_at`, `category_id`, `image_url`) VALUES
(1, 'AI startups raise record funding in Q1 2026', 'Venture capital investment in AI startups reached unprecedented levels in the first quarter of 2026, with total funding exceeding $50 billion globally. The surge was driven by enterprise demand for generative AI solutions and autonomous systems.', 'https://example.com/articles/ai-funding-q1-2026', 'Sarah Chen', '2026-03-15 10:00:00', 1, 'https://picsum.photos/800/400?random=1'),
(2, 'New Linux kernel release brings major performance improvements', 'The latest Linux kernel update introduces significant optimizations for modern hardware, including improved memory management and faster I/O operations for NVMe storage devices.', 'https://example.com/articles/linux-kernel-perf', 'James Wilson', '2026-03-14 14:30:00', 1, 'https://picsum.photos/800/400?random=2'),
(4, 'Global climate summit reaches historic agreement', 'World leaders gathered at the 2026 Climate Action Summit have agreed on binding emissions targets, marking a significant step forward in international climate cooperation.', 'https://example.com/articles/climate-summit-2026', 'Maria Garcia', '2026-03-13 09:15:00', 4, 'https://picsum.photos/800/400?random=3'),
(6, 'NASA announces new Mars mission for 2028', 'NASA has unveiled plans for a new robotic mission to Mars, aimed at collecting samples from previously unexplored regions of the planet surface.', 'https://example.com/articles/nasa-mars-2028', 'Dr. Robert Kim', '2026-03-12 16:45:00', 2, 'https://picsum.photos/800/400?random=4'),
(8, 'Tech giants report strong earnings amid AI boom', 'Major technology companies have reported better-than-expected quarterly results, driven primarily by growing demand for AI infrastructure and cloud services.', 'https://example.com/articles/tech-earnings-ai', 'Emily Watson', '2026-03-11 11:20:00', 3, 'https://picsum.photos/800/400?random=5'),
(3, 'Understanding modern PHP: from 7.4 to 8.3', 'A comprehensive overview of the evolution of PHP over the past several years, highlighting key language features, performance improvements, and the growing ecosystem.', 'https://example.com/articles/modern-php-overview', 'Alexei Petrov', '2026-03-10 08:00:00', 1, 'https://picsum.photos/800/400?random=6'),
(7, 'Breakthrough in quantum computing error correction', 'Researchers have demonstrated a new approach to quantum error correction that could make practical quantum computers viable within the next decade.', 'https://example.com/articles/quantum-error-correction', 'Dr. Lisa Chang', '2026-03-09 13:10:00', 2, 'https://picsum.photos/800/400?random=7'),
(9, 'Streaming wars heat up with new platform launches', 'The entertainment streaming landscape continues to evolve as two major new platforms enter the market, intensifying competition for subscriber attention.', 'https://example.com/articles/streaming-wars-2026', 'Mike Thompson', '2026-03-08 15:30:00', 5, 'https://picsum.photos/800/400?random=8'),
(5, 'EU approves landmark digital regulation package', 'The European Union has passed a comprehensive digital regulation framework that will reshape how technology companies operate across the continent.', 'https://example.com/articles/eu-digital-regulation', 'Sophie Laurent', '2026-03-07 10:45:00', 4, 'https://picsum.photos/800/400?random=9'),
(10, 'Open source LLM ecosystem grows rapidly', 'The open-source large language model community has seen explosive growth, with several new models matching or exceeding proprietary alternatives in key benchmarks.', 'https://example.com/articles/open-source-llm-growth', 'David Park', '2026-03-06 12:00:00', 1, 'https://picsum.photos/800/400?random=10');
