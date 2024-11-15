
# AI Teaching Assistant Platform

A WordPress-based platform designed to help content creators teach AI concepts through YouTube and other mediums.

## 🚀 Quick Start with Docker

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Installation

1. Clone this repository
   ```bash
   git clone [your-repository-url]
   cd [repository-name]/docker
   ```

2. Start the containers
   ```bash
   docker-compose up -d
   ```

3. Access WordPress
   - Open your browser and navigate to `http://localhost:8080`
   - Complete the WordPress installation:
     - Username: `admin`
     - Password: `password`
     - Email: Your email

### Plugin Setup

1. Log in to WordPress admin panel (`http://localhost:8080/wp-admin`)
2. Go to Plugins → Installed Plugins
3. Activate both plugins:
   - ChatGPT Assistant Plugin
   - AI Blog Writer Plugin

## 💻 Development

The project includes two main plugins:

- `/wp-content/plugins/chatgpt-assistant-plugin/` - AI teaching assistant functionality
- `/wp-content/plugins/ai-blog-writer-plugin/` - AI content generation tools

## 🚀 Deployment

To use these plugins on a live WordPress site:

1. Zip the plugin folders:
   ```bash
   zip -r chatgpt-assistant-plugin.zip chatgpt-assistant-plugin/
   zip -r ai-blog-writer-plugin.zip ai-blog-writer-plugin/
   ```

2. Upload to your live site:
   - Go to Plugins → Add New → Upload Plugin
   - Upload and activate each zip file
   - Configure settings as needed

## 🛠️ Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Restart containers
docker-compose restart
```

## 📁 Project Structure

```
project-root/
├── docker/
│   └── docker-compose.yml
└── wp-content/
    └── plugins/
        ├── chatgpt-assistant-plugin/
        └── ai-blog-writer-plugin/
```

## 🚨 Troubleshooting

1. **Cannot access WordPress**
   - Verify Docker is running
   - Check if port 8080 is available
   - Try `docker-compose restart`

2. **Plugins not working**
   - Check plugin activation status
   - Clear WordPress cache
   - Review error logs in wp-admin

## 📝 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request


---

Made with ❤️ for AI educators and learners
# word-press-ai-template
