export type Post = {
  id: number;
  publishedAt: string;
  author: string;
  title: string;
  body: string;
  tags?: string[];
  categories?: string[];
};

export type PostListResponse = Post[];
