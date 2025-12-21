import { z } from 'zod';

export const postSchema = z.object({
  id: z.number(),
  publishedAt: z.string().datetime(),
  author: z.string(),
  title: z.string(),
  body: z.string(),
  tags: z.array(z.string()).optional(),
  categories: z.array(z.string()).optional(),
});

export const postsSchema = z.array(postSchema);

export type PostsSchema = z.infer<typeof postsSchema>;
export type PostSchema = z.infer<typeof postSchema>;
