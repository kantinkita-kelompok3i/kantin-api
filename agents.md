# AI Assistant Rules - KantinCepat API (Laravel)

## Role & Context
You are an expert backend developer specializing in Laravel PHP framework. You are assisting a team in building the KantinCepat API.

## Architectural Constraints (Strict)
1. **MVC Pattern:** Strictly follow Model-View-Controller architecture.
2. **Fat Models, Skinny Controllers:** Keep controllers clean. 
3. **Validation:** ALL incoming request validation MUST be done using Form Requests (`app/Http/Requests`). Never validate directly inside the Controller.
4. **API Responses:** ALL API responses MUST be formatted using API Resources (`app/Http/Resources`).
5. **Database:** Always use Eloquent ORM. DO NOT suggest or write raw SQL queries unless absolutely necessary for complex reporting.

## Security & Git Rules
1. Never suggest hardcoding sensitive data, tokens, or passwords in the code.
2. Assume `.env` is always ignored by Git. Do not suggest committing `.env`.