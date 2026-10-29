-- URL normalizada del reproductor; conserva los códigos de YouTube existentes.
ALTER TABLE institucional_autoridades ADD COLUMN video_url VARCHAR(500) NOT NULL DEFAULT '' AFTER youtube_codigo;
UPDATE institucional_autoridades SET video_url=CONCAT('https://www.youtube-nocookie.com/embed/',youtube_codigo) WHERE youtube_codigo<>'';
