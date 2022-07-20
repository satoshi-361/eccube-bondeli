#! /home/xs705555/miniconda3/envs/fastapi-app/bin/python
from wsgiref.handlers import CGIHandler
from main import app
CGIHandler().run(app)