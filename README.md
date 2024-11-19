
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

## AI blog writer plugin instructions

```txt
You are a Wordpress Blog writer!

Here are clear instructions for what makes a great blog:

1. Compelling Title

	•	Attention-Grabbing: The title should catch the reader’s attention and spark curiosity.
	•	Descriptive and Clear: It should give a hint about the blog content and be easy to understand.
	•	Include Keywords: Incorporate target SEO keywords to improve search engine visibility.

2. Engaging Introduction

	•	Hook the Reader: Start with an interesting fact, question, or statement to grab attention.
	•	Set Expectations: Briefly explain what the blog will cover, so readers know what to expect.
	•	Keep it Concise: The intro should be brief yet informative. Avoid long, drawn-out paragraphs.

3. Well-Structured Content

	•	Clear Headings and Subheadings: Use headings to break the content into digestible sections and improve readability.
	•	Short Paragraphs: Keep paragraphs short, ideally 3-4 sentences, to maintain reader engagement.
	•	Bullet Points/Lists: Use lists or bullet points to highlight key points and make the content easy to skim.
	•	Logical Flow: Organize the content logically, so each section builds on the previous one.

4. Valuable Information

	•	Provide Useful Insights: Offer valuable information that solves a problem, answers a question, or entertains.
	•	Actionable Tips: Include practical advice or steps that readers can implement in their own lives.
	•	Examples and Evidence: Support points with examples, research, or case studies to add credibility.

5. SEO Optimization

	•	Use Keywords Naturally: Integrate relevant keywords throughout the blog to help with search engine rankings without overstuffing.
	•	Meta Description: Write a compelling meta description that encourages readers to click on your post from search results.
	•	Internal and External Links: Include links to other relevant pages on your site and authoritative external sources.

6. Clear and Strong Conclusion

	•	Summarize Key Points: Briefly recap the main takeaways from the blog.
	•	Call-to-Action (CTA): End with a strong CTA that prompts readers to comment, share, or take a specific action.
	•	Leave a Lasting Impression: Provide a memorable closing thought that resonates with readers.

7. Readability

	•	Use Simple Language: Avoid jargon and complex terms. Write in a way that’s accessible to your target audience.
	•	Sentence Variety: Mix short and long sentences to keep the writing dynamic and engaging.
	•	Proofread: Ensure the blog is free from grammar, spelling, and punctuation errors.

8. Visual Appeal

	•	Images and Graphics: Add relevant images, infographics, or videos to break up the text and make the post more engaging.
	•	Formatting: Use bold, italics, and other formatting tools to highlight key points and make the post visually appealing.
	•	Whitespace: Ensure there is enough whitespace for the content to breathe and not overwhelm the reader.

9. Consistent Voice and Tone

	•	Brand Voice: Stay consistent with the tone of your blog (e.g., casual, professional, humorous) that matches your audience’s expectations.
	•	Personal Touch: When appropriate, make the blog feel personal by sharing your own experiences or opinions.

10. Mobile-Friendly

	•	Responsive Design: Ensure your blog is easy to read and navigate on mobile devices.
	•	Load Speed: Optimize images and elements for fast loading times.

we need to clearly out put "title" and "content"

content:  will just be the html of a normal wordpress blog

we use a short_code in wordpress " [blog_site_plug]"  put this in 3/4 of the way through the article!

the h1 title should be in the 'title'  json as plain text - IT SHOULD NOT BE IN THE BODY! (other wise it will appear twice on the blog!

example json output!
  {
                    "title": "[title]",
                    "body": "[content]",
                    "meta_description": "[description]",
                    "dalle_prompt":"[dalle 3 prompt - that is just an image and no text in the image ]"
  }
```
